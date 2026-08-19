"use client";

import { useEffect, useRef } from "react";
import { usePrefersReducedMotion } from "@/lib/useInView";

/* ------------------------------------------------------------------ *
 *  Ball flight
 *
 *  Integrated in yards/seconds with drag and a Magnus lift term, then
 *  normalised so the shot carries the distance we asked for. The point
 *  isn't simulation accuracy — it's that the *shape* is real: a fast
 *  climb, a late apex, and a markedly steeper descent than ascent. A
 *  symmetric parabola reads as fake immediately to anyone who plays.
 * ------------------------------------------------------------------ */

const G = 10.725; // yd/s^2
const K_DRAG = 0.0031;
const K_LIFT = 0.0021;

type Vec3 = { x: number; y: number; z: number };

function simulate(
  ballSpeedMph: number,
  launchDeg: number,
  azimuthDeg: number,
  spinAxisDeg: number,
  targetCarry: number
): Vec3[] {
  const v0 = ballSpeedMph * 0.4889; // mph -> yd/s
  const la = (launchDeg * Math.PI) / 180;
  const az = (azimuthDeg * Math.PI) / 180;
  const axis = (spinAxisDeg * Math.PI) / 180;

  let p: Vec3 = { x: 0, y: 0, z: 0 };
  let v: Vec3 = {
    x: v0 * Math.cos(la) * Math.cos(az),
    y: v0 * Math.sin(la),
    z: v0 * Math.cos(la) * Math.sin(az),
  };

  const pts: Vec3[] = [{ ...p }];
  const dt = 1 / 120;

  for (let i = 0; i < 2400; i++) {
    const speed = Math.hypot(v.x, v.y, v.z) || 1e-6;

    // Drag opposes velocity.
    const drag = K_DRAG * speed;
    // Lift acts perpendicular to travel; tilting the spin axis pushes
    // some of it sideways, which is what bends a draw or a fade.
    const lift = K_LIFT * speed * speed;

    const ax = -drag * v.x;
    const ay = -drag * v.y + lift * Math.cos(axis) - G;
    const azc = -drag * v.z + lift * Math.sin(axis);

    v = { x: v.x + ax * dt, y: v.y + ay * dt, z: v.z + azc * dt };
    p = { x: p.x + v.x * dt, y: p.y + v.y * dt, z: p.z + v.z * dt };

    pts.push({ ...p });
    if (p.y <= 0 && i > 4) break;
  }

  // Scale downrange so the carry matches the number we advertise.
  const carried = pts[pts.length - 1].x || 1;
  const s = targetCarry / carried;
  return pts.map((q) => ({ x: q.x * s, y: q.y * s, z: q.z * s }));
}

/* ------------------------------------------------------------------ */

type Shot = {
  pts: Vec3[];
  t: number; // 0..1 progress of the head
  dur: number; // seconds of flight
  delay: number; // seconds before launch
  fade: number; // 0..1 post-landing fade out
  hue: "lime" | "amber";
};

/* Azimuths are spread wide on purpose. Real drives off one tee sit
 * within a few degrees of each other, which projects to a bundle of
 * near-vertical lines — accurate, and unreadable. Fanning them reads as
 * what it actually is: a range with every bay hitting at once. */
const SHOT_SPECS = [
  { speed: 167, launch: 12.8, az: -21, axis: 7, carry: 289, hue: "lime" as const },
  { speed: 149, launch: 16.4, az: -9, axis: -11, carry: 232, hue: "amber" as const },
  { speed: 158, launch: 10.2, az: 2, axis: 4, carry: 262, hue: "lime" as const },
  { speed: 138, launch: 21.5, az: 13, axis: 9, carry: 196, hue: "lime" as const },
  { speed: 172, launch: 14.1, az: 23, axis: -6, carry: 301, hue: "amber" as const },
];

export function ShotTracer({ className = "" }: { className?: string }) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const reduced = usePrefersReducedMotion();

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d", { alpha: true });
    if (!ctx) return;

    let raf = 0;
    let running = true;
    let W = 0;
    let H = 0;

    const shots: Shot[] = SHOT_SPECS.map((s, i) => ({
      pts: simulate(s.speed, s.launch, s.az, s.axis, s.carry),
      t: 0,
      dur: 2.6 + i * 0.18,
      delay: i * 1.45,
      fade: 0,
      hue: s.hue,
    }));

    const resize = () => {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      const r = canvas.getBoundingClientRect();
      W = r.width;
      H = r.height;
      canvas.width = Math.round(W * dpr);
      canvas.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    };

    resize();
    const ro = new ResizeObserver(resize);
    ro.observe(canvas);

    /* Camera sits just behind and above the tee, looking downrange. The
     * result is that every tracer converges on the horizon — the thing
     * that makes it read as distance rather than as a 2D squiggle. */
    const CAM = { back: 12, height: 2.5 };

    /* Two focal lengths, deliberately. A single one ties the lateral
     * fan to viewport height, which collapses the whole spread into a
     * vertical squiggle on a narrow phone. Splitting them lets the fan
     * breathe with width while the arc keeps its height. */
    const focal = () => ({ fx: W * 0.78, fy: Math.max(W, H) * 0.55 });

    const project = (p: Vec3) => {
      const { fx, fy } = focal();
      const dx = p.x + CAM.back;
      return {
        x: W * 0.5 + (fx * p.z) / dx,
        y: H * 0.42 - (fy * (p.y - CAM.height)) / dx,
        // Tapers with distance, but never below ~1px — a hairline that
        // thin simply disappears against the photograph.
        w: Math.max(1.15, Math.min(4.5, 60 / dx)),
      };
    };

    const drawGround = () => {
      const horizon = H * 0.42;
      const { fx: f } = focal();
      ctx.save();
      // Distance bands, converging toward the horizon.
      for (const yd of [50, 100, 150, 200, 250, 300]) {
        const p = project({ x: yd, y: 0, z: 0 });
        const spread = (f * 60) / (yd + CAM.back);
        ctx.beginPath();
        ctx.moveTo(W * 0.5 - spread, p.y);
        ctx.lineTo(W * 0.5 + spread, p.y);
        ctx.strokeStyle = "rgba(198,242,78," + Math.max(0, 0.05 - yd * 0.00012) + ")";
        ctx.lineWidth = 1;
        ctx.stroke();
      }
      // Horizon haze.
      const g = ctx.createLinearGradient(0, horizon - 60, 0, horizon + 90);
      g.addColorStop(0, "rgba(198,242,78,0)");
      g.addColorStop(0.5, "rgba(198,242,78,0.045)");
      g.addColorStop(1, "rgba(198,242,78,0)");
      ctx.fillStyle = g;
      ctx.fillRect(0, horizon - 60, W, 150);
      ctx.restore();
    };

    const drawShot = (s: Shot) => {
      const n = s.pts.length;
      const head = Math.floor(s.t * (n - 1));
      if (head < 1) return;

      const rgb = s.hue === "lime" ? "198,242,78" : "240,160,90";
      const alpha = 1 - s.fade;

      ctx.save();
      ctx.lineCap = "round";
      ctx.lineJoin = "round";

      // Trail drawn segment-wise so it can taper in both width and
      // opacity — a single stroked path cannot do that.
      for (let i = 1; i <= head; i++) {
        const a = project(s.pts[i - 1]);
        const b = project(s.pts[i]);
        const age = i / head;
        const tail = Math.pow(age, 0.55);

        ctx.beginPath();
        ctx.moveTo(a.x, a.y);
        ctx.lineTo(b.x, b.y);
        ctx.strokeStyle = "rgba(" + rgb + "," + 0.9 * tail * alpha + ")";
        ctx.lineWidth = b.w * (0.65 + 0.35 * tail);
        ctx.stroke();
      }

      // Glow pass over the live end of the trail.
      const from = Math.max(1, head - 26);
      ctx.beginPath();
      const p0 = project(s.pts[from]);
      ctx.moveTo(p0.x, p0.y);
      for (let i = from + 1; i <= head; i++) {
        const p = project(s.pts[i]);
        ctx.lineTo(p.x, p.y);
      }
      ctx.strokeStyle = "rgba(" + rgb + "," + 0.55 * alpha + ")";
      ctx.lineWidth = 9;
      ctx.filter = "blur(8px)";
      ctx.stroke();
      ctx.filter = "none";

      // The ball.
      const h = project(s.pts[head]);
      const rad = Math.max(2, h.w * 1.5);
      const grad = ctx.createRadialGradient(h.x, h.y, 0, h.x, h.y, rad * 5);
      grad.addColorStop(0, "rgba(255,255,255," + 0.95 * alpha + ")");
      grad.addColorStop(0.25, "rgba(" + rgb + "," + 0.75 * alpha + ")");
      grad.addColorStop(1, "rgba(" + rgb + ",0)");
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.arc(h.x, h.y, rad * 5, 0, Math.PI * 2);
      ctx.fill();

      ctx.fillStyle = "rgba(255,255,255," + alpha + ")";
      ctx.beginPath();
      ctx.arc(h.x, h.y, rad * 0.8, 0, Math.PI * 2);
      ctx.fill();

      ctx.restore();
    };

    let last = performance.now();
    let clock = 0;
    const CYCLE = 9;

    const frame = (now: number) => {
      if (!running) return;
      const dt = Math.min(0.05, (now - last) / 1000);
      last = now;
      clock += dt;

      ctx.clearRect(0, 0, W, H);
      drawGround();

      for (const s of shots) {
        const local = (clock - s.delay + CYCLE * 10) % CYCLE;
        if (local > s.dur + 2.2) {
          s.t = 0;
          s.fade = 1;
          continue;
        }
        if (local <= s.dur) {
          s.t = local / s.dur;
          s.fade = 0;
        } else {
          s.t = 1;
          s.fade = Math.min(1, (local - s.dur) / 2.2);
        }
        drawShot(s);
      }

      raf = requestAnimationFrame(frame);
    };

    // A static frame is the right answer for reduced motion — the
    // composition still reads, nothing moves.
    if (reduced) {
      ctx.clearRect(0, 0, W, H);
      drawGround();
      for (const s of shots.slice(0, 3)) {
        s.t = 1;
        s.fade = 0.35;
        drawShot(s);
      }
    } else {
      raf = requestAnimationFrame(frame);
    }

    const onVisibility = () => {
      if (document.hidden) {
        running = false;
        cancelAnimationFrame(raf);
      } else if (!reduced) {
        running = true;
        last = performance.now();
        raf = requestAnimationFrame(frame);
      }
    };
    document.addEventListener("visibilitychange", onVisibility);

    return () => {
      running = false;
      cancelAnimationFrame(raf);
      ro.disconnect();
      document.removeEventListener("visibilitychange", onVisibility);
    };
  }, [reduced]);

  return <canvas ref={canvasRef} aria-hidden className={className} />;
}
