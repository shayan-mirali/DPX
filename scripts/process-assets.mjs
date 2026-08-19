import sharp from "sharp";
import { mkdir } from "node:fs/promises";

const SRC = "_assets";
const OUT = "public/img";
await mkdir(OUT, { recursive: true });

const log = (...a) => console.log("  ", ...a);

/* ------------------------------------------------------------------ *
 * 1. Venue photo — the source is a phone screenshot with black
 *    letterbox bars. Detect the real photo band and crop to it.
 * ------------------------------------------------------------------ */
async function cropLetterbox() {
  const src = `${SRC}/images/WhatsApp Image 2026-08-18 at 4.00.29 PM.jpeg2233.jpeg`;
  const img = sharp(src);
  const { width, height } = await img.metadata();
  const { data, info } = await img
    .clone()
    .greyscale()
    .raw()
    .toBuffer({ resolveWithObject: true });

  // Mean brightness per row; letterbox rows sit near zero.
  const rowMean = [];
  for (let y = 0; y < info.height; y++) {
    let sum = 0;
    for (let x = 0; x < info.width; x++) sum += data[y * info.width + x];
    rowMean.push(sum / info.width);
  }
  const THRESH = 12;
  let top = rowMean.findIndex((m) => m > THRESH);
  let bottom = rowMean.length - 1 - [...rowMean].reverse().findIndex((m) => m > THRESH);
  if (top < 0) { top = 0; bottom = height - 1; }

  const h = bottom - top + 1;
  log(`venue: ${width}x${height} -> content band y=${top}..${bottom} (${width}x${h})`);

  const base = sharp(src).extract({ left: 0, top, width, height: h });

  // Full-width plate, upscaled with a gentle grade. The source is small,
  // so we lean on contrast + saturation rather than fake sharpening.
  await base
    .clone()
    .resize({ width: 2200, kernel: "lanczos3" })
    .modulate({ brightness: 1.04, saturation: 1.14 })
    .linear(1.12, -12)
    .webp({ quality: 88 })
    .toFile(`${OUT}/venue-wide.webp`);

  // Portrait crop for mobile / card use — take the left half where the
  // sim screen and seating are.
  await base
    .clone()
    .extract({ left: 0, top: 0, width: Math.round(width * 0.62), height: h })
    .resize({ width: 1100, kernel: "lanczos3" })
    .modulate({ brightness: 1.04, saturation: 1.14 })
    .linear(1.12, -12)
    .webp({ quality: 88 })
    .toFile(`${OUT}/venue-tall.webp`);

  // Tiny blur for the LQIP placeholder.
  const blur = await base
    .clone()
    .resize({ width: 24 })
    .blur(2)
    .webp({ quality: 40 })
    .toBuffer();
  log(`venue: wrote venue-wide.webp, venue-tall.webp, lqip ${blur.length}b`);
  return `data:image/webp;base64,${blur.toString("base64")}`;
}

/* ------------------------------------------------------------------ *
 * 2. Logos — flatten JPEGs into transparent PNGs by deriving alpha
 *    from luminance, then recolouring the mark.
 * ------------------------------------------------------------------ */
async function knockout(src, out, { mode, rgb, trimPad = 8 }) {
  const { data, info } = await sharp(src)
    .ensureAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });

  const { width, height, channels } = info;
  const px = Buffer.alloc(width * height * 4);

  for (let i = 0, j = 0; i < data.length; i += channels, j += 4) {
    const r = data[i], g = data[i + 1], b = data[i + 2];
    const lum = (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;

    let a;
    if (mode === "dark-on-light") {
      // Black artwork on white: darker == more opaque.
      a = 1 - lum;
    } else {
      // White artwork on a mid-tone tile: brighter == more opaque.
      const floor = 0.78; // sits above the orange plate so it cuts to zero
      a = (lum - floor) / (1 - floor);
    }
    a = Math.max(0, Math.min(1, a));
    // Push through a slight curve so antialiased edges stay clean
    // instead of turning into a grey halo.
    a = a < 0.06 ? 0 : Math.pow(a, 0.85);

    px[j] = rgb[0];
    px[j + 1] = rgb[1];
    px[j + 2] = rgb[2];
    px[j + 3] = Math.round(a * 255);
  }

  await sharp(px, { raw: { width, height, channels: 4 } })
    .png()
    .toBuffer()
    .then((buf) =>
      sharp(buf)
        .trim({ threshold: 1 })
        .extend({
          top: trimPad, bottom: trimPad, left: trimPad, right: trimPad,
          background: { r: 0, g: 0, b: 0, alpha: 0 },
        })
        .png({ compressionLevel: 9 })
        .toFile(out)
    );

  const meta = await sharp(out).metadata();
  log(`${out}: ${meta.width}x${meta.height}`);
}

const lqip = await cropLetterbox();

const DPX = `${SRC}/logos/WhatsApp Image 2026-08-18 at 4.00.29 PM.jpeg`;
await knockout(DPX, `${OUT}/dpx-bone.png`,  { mode: "dark-on-light", rgb: [237, 232, 220] });
await knockout(DPX, `${OUT}/dpx-ink.png`,   { mode: "dark-on-light", rgb: [7, 12, 11] });
await knockout(DPX, `${OUT}/dpx-lime.png`,  { mode: "dark-on-light", rgb: [198, 242, 78] });

const TM = `${SRC}/images/WhatsApp Image 2026-08-18 at 4.00.29 PM.jpeg`;
await knockout(TM, `${OUT}/trackman-bone.png`, { mode: "light-on-dark", rgb: [237, 232, 220] });

console.log("\nLQIP data URI length:", lqip.length);
console.log(lqip);

/* ------------------------------------------------------------------ *
 * 3. Favicon — crop the reticle mark out of the lockup and set it on
 *    the ink background so it stays legible in a browser tab.
 * ------------------------------------------------------------------ */
const mark = await sharp(`${OUT}/dpx-lime.png`)
  .extract({ left: 0, top: 0, width: 300, height: 443 })
  .resize(340, 340, { fit: "contain", background: { r: 0, g: 0, b: 0, alpha: 0 } })
  .toBuffer();

await sharp({
  create: { width: 512, height: 512, channels: 4, background: { r: 6, g: 10, b: 9, alpha: 1 } },
})
  .composite([{ input: mark, gravity: "center" }])
  .png()
  .toFile("src/app/icon.png");

log("src/app/icon.png: 512x512");
