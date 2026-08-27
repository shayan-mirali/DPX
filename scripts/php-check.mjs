/**
 * Checks the PHP build without a PHP runtime.
 *
 * Parses every file in php/ and reports syntax errors, calls to functions
 * that are never defined, and uses of undefined constants — the two bug
 * classes most likely to survive review in templates nobody can execute
 * locally. Run it after editing anything under php/.
 *
 *   npm run check:php
 */
import { createRequire } from "node:module";
import { readFileSync, readdirSync, statSync } from "node:fs";
import { join, relative } from "node:path";
import { fileURLToPath } from "node:url";

const require = createRequire(import.meta.url);
const Engine = require("php-parser");
const parser = new Engine({ parser: { suppressErrors: false, version: 802 }, ast: { withPositions: true } });

const ROOT = fileURLToPath(new URL("../php", import.meta.url));

// PHP builtins the templates legitimately use.
const BUILTINS = new Set([
  "htmlspecialchars", "implode", "explode", "count", "array_search", "sort", "substr",
  "array_map", "array_filter", "array_values", "array_merge", "preg_replace", "preg_match",
  "preg_split", "str_pad", "number_format", "floor", "min", "max", "date", "json_encode",
  "json_decode", "rawurlencode", "trim", "filter_var", "mb_strlen", "strlen", "time",
  "is_file", "is_dir", "mkdir", "file_get_contents", "file_put_contents", "is_array",
  "function_exists", "curl_init", "curl_setopt_array", "curl_exec", "curl_getinfo",
  "curl_error", "curl_close", "http_response_code", "header", "error_log", "sprintf",
  "gmdate", "str_contains", "nl2br", "is_int", "unset", "empty", "isset", "exit",
  "filemtime", "array_key_exists", "strval", "intval", "abs", "round", "ucfirst",
  "unlink", "require", "include", "printf", "str_replace", "in_array", "array_slice",
  "htmlspecialchars_decode", "rtrim", "ltrim", "strtolower", "strtoupper", "sprintf",
  // Admin dashboard: sessions, hashing, files, CSV.
  "session_start", "session_status", "session_name", "session_destroy",
  "session_regenerate_id", "session_set_cookie_params", "session_get_cookie_params",
  "setcookie", "ini_get", "password_hash", "password_verify", "hash_equals",
  "random_bytes", "bin2hex", "define", "dirname", "rename", "glob",
  "fopen", "fclose", "fwrite", "fputcsv", "file", "strtotime",
  "array_is_list", "array_reverse", "array_shift", "array_unique", "array_intersect",
  "is_numeric", "is_string", "mb_strtolower", "mb_strtoupper", "str_pad",
  "array_keys", "array_values", "array_merge", "array_combine", "array_sum",
  "usort", "uasort", "ksort", "asort", "number_format", "json_last_error",
]);

// Magic and extension constants that are always available.
const RUNTIME_CONST = [
  "PHP_VERSION_ID", "PHP_VERSION", "PHP_EOL", "CURLOPT_NOBODY", "CURLOPT_URL",
  "ENT_HTML5", "M_PI", "PHP_INT_MAX", "PASSWORD_DEFAULT", "PHP_SESSION_ACTIVE",
  "FILE_IGNORE_NEW_LINES", "FILE_SKIP_EMPTY_LINES", "JSON_PRETTY_PRINT",
];

function walk(dir, out = []) {
  for (const n of readdirSync(dir)) {
    const p = join(dir, n);
    if (statSync(p).isDirectory()) walk(p, out);
    else if (p.endsWith(".php")) out.push(p);
  }
  return out;
}

const defined = new Set();
const constants = new Set();
const calls = [];
const constUses = [];

function visit(node, file) {
  if (!node || typeof node !== "object") return;

  if (node.kind === "function" && node.name) {
    defined.add(typeof node.name === "string" ? node.name : node.name.name);
  }
  if (node.kind === "constantstatement" || node.kind === "classconstant") {
    (node.constants || []).forEach((c) => {
      constants.add(typeof c.name === "string" ? c.name : c.name.name);
    });
  }
  if (node.kind === "call" && node.what) {
    // Only real function names. `$closure(...)` is a `variable` node and is
    // resolved at runtime, not a function-name lookup.
    if (node.what.kind === "name") {
      const n = node.what.name;
      if (typeof n === "string") calls.push({ name: n, file, line: node.loc?.start?.line });
    }
  }
  if (node.kind === "staticlookup" || node.kind === "constref") {
    const n = typeof node.name === "string" ? node.name : node.name?.name;
    if (n && /^[A-Z][A-Z_0-9]*$/.test(n)) constUses.push({ name: n, file, line: node.loc?.start?.line });
  }

  for (const k of Object.keys(node)) {
    const v = node[k];
    if (Array.isArray(v)) v.forEach((c) => visit(c, file));
    else if (v && typeof v === "object" && v.kind) visit(v, file);
  }
}

const files = walk(ROOT);
for (const f of files) {
  visit(parser.parseCode(readFileSync(f, "utf8"), f), f.replace(ROOT, "php").replace(/\\/g, "/"));
}

console.log("defined functions:", [...defined].sort().join(", "));
console.log("defined constants:", [...constants].sort().join(", "));

let bad = 0;
console.log("\nundefined function calls:");
const seen = new Set();
for (const c of calls) {
  if (defined.has(c.name) || BUILTINS.has(c.name)) continue;
  const key = c.name + c.file;
  if (seen.has(key)) continue;
  seen.add(key);
  bad++;
  console.log(`  FAIL  ${c.name}()  ${c.file}:${c.line}`);
}
if (!bad) console.log("  none");

console.log("\nundefined constant uses:");
let badC = 0;
const seenC = new Set();
const KNOWN_CONST = new Set([...RUNTIME_CONST,"ENT_QUOTES","ENT_SUBSTITUTE","STR_PAD_LEFT","PHP_EOL","LOCK_EX","FILE_APPEND",
  "JSON_UNESCAPED_UNICODE","JSON_UNESCAPED_SLASHES","FILTER_VALIDATE_EMAIL","CURLOPT_RETURNTRANSFER",
  "CURLOPT_POST","CURLOPT_TIMEOUT","CURLOPT_HTTPHEADER","CURLOPT_POSTFIELDS","CURLINFO_HTTP_CODE"]);
for (const c of constUses) {
  if (constants.has(c.name) || KNOWN_CONST.has(c.name)) continue;
  const key = c.name + c.file;
  if (seenC.has(key)) continue;
  seenC.add(key);
  badC++;
  console.log(`  FAIL  ${c.name}  ${c.file}:${c.line}`);
}
if (!badC) console.log("  none");

console.log(`\n${bad + badC} problem(s)`);
process.exit(bad + badC ? 1 : 0);
