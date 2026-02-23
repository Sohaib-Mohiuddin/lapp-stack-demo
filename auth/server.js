const http = require("node:http");
const { URL } = require("node:url");
const crypto = require("node:crypto");
const jwt = require("jsonwebtoken");

const PORT = Number(process.env.AUTH_PORT || 3000);

const JWT_SECRET = process.env.JWT_SECRET;
const ISSUER = process.env.JWT_ISSUER;
const AUDIENCE = process.env.JWT_AUDIENCE;
const ACCESS_TTL = Number(process.env.JWT_ACCESS_TTL_SECONDS || 900);
const REFRESH_TTL = Number(process.env.JWT_REFRESH_TTL_SECONDS || 604800);

// Simple in-memory user store for demo.
// Enterprise: DB + hashed passwords + lockout policies + MFA + IdP (OIDC).
function loadUsersFromEnv() {
  const adminUser = process.env.AUTH_USER_ADMIN
  const adminPass = process.env.AUTH_PASS_ADMIN
  const staffUser = process.env.AUTH_USER_STAFF
  const staffPass = process.env.AUTH_PASS_STAFF

  return [
    { username: adminUser, password: adminPass, role: "admin" },
    { username: staffUser, password: staffPass, role: "staff" }
  ];
}

const USERS = loadUsersFromEnv();

// For demo, hash passwords at runtime.
// Enterprise: store salted hashes at rest.
function hashPassword(pw) {
  // fast-ish demo hash (avoid teaching insecure MD5/SHA1)
  return crypto.createHash("sha256").update(pw).digest("hex");
}
const USER_HASHES = USERS.map(u => ({
  username: u.username,
  pwHash: hashPassword(u.password),
  role: u.role
}));

function json(res, status, bodyObj) {
  const body = JSON.stringify(bodyObj);
  res.writeHead(status, {
    "Content-Type": "application/json; charset=utf-8",
    "Content-Length": Buffer.byteLength(body)
  });
  res.end(body);
}

function readJson(req) {
  return new Promise((resolve, reject) => {
    let data = "";
    req.on("data", chunk => (data += chunk));
    req.on("end", () => {
      if (!data) return resolve({});
      try {
        resolve(JSON.parse(data));
      } catch (e) {
        reject(new Error("Invalid JSON"));
      }
    });
  });
}

function signAccessToken({ sub, role }) {
  return jwt.sign(
    { role },
    JWT_SECRET,
    {
      algorithm: "HS256",
      issuer: ISSUER,
      audience: AUDIENCE,
      subject: sub,
      expiresIn: ACCESS_TTL
    }
  );
}

function signRefreshToken({ sub, role }) {
  return jwt.sign(
    { role, typ: "refresh" },
    JWT_SECRET,
    {
      algorithm: "HS256",
      issuer: ISSUER,
      audience: AUDIENCE,
      subject: sub,
      expiresIn: REFRESH_TTL
    }
  );
}

function verifyToken(token) {
  return jwt.verify(token, JWT_SECRET, {
    algorithms: ["HS256"],
    issuer: ISSUER,
    audience: AUDIENCE
  });
}

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, `http://${req.headers.host}`);
  const path = url.pathname;
  const method = req.method || "GET";

  // Health
  if (method === "GET" && path === "/health") {
    return json(res, 200, { status: "ok" });
  }

  // Login
  if (method === "POST" && path === "/auth/login") {
    try {
      const body = await readJson(req);
      const username = String(body.username || "").trim();
      const password = String(body.password || "");

      if (!username || !password) {
        return json(res, 400, { error: "username and password required" });
      }

      const found = USER_HASHES.find(u => u.username === username);
      if (!found) return json(res, 401, { error: "invalid credentials" });

      const providedHash = hashPassword(password);
      // timing-safe compare
      const ok =
        providedHash.length === found.pwHash.length &&
        crypto.timingSafeEqual(Buffer.from(providedHash), Buffer.from(found.pwHash));

      if (!ok) return json(res, 401, { error: "invalid credentials" });

      const access_token = signAccessToken({ sub: username, role: found.role });
      const refresh_token = signRefreshToken({ sub: username, role: found.role });

      return json(res, 200, {
        token_type: "Bearer",
        access_token,
        expires_in: ACCESS_TTL,
        refresh_token
      });
    } catch (e) {
      return json(res, 400, { error: e.message || "bad request" });
    }
  }

  // Verify (used by other services)
  if (method === "POST" && path === "/auth/verify") {
    try {
      const auth = req.headers["authorization"] || "";
      const token = auth.startsWith("Bearer ") ? auth.slice(7) : "";

      if (!token) return json(res, 401, { error: "missing token" });

      const claims = verifyToken(token);

      return json(res, 200, {
        active: true,
        sub: claims.sub,
        role: claims.role,
        exp: claims.exp,
        iss: claims.iss,
        aud: claims.aud
      });
    } catch (e) {
      return json(res, 401, { active: false, error: "invalid token" });
    }
  }

  return json(res, 404, { error: "not found" });
});

server.listen(PORT, "0.0.0.0", () => {
  console.log(`auth-service listening on ${PORT}`);
});
