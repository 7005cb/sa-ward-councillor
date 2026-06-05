# Hermes Agent (Linux Mint) → Ollama VPS via SSH Tunnel
# Production Setup Prompt for Claude Code

## Goal
Set up Hermes Agent on the local Linux Mint PC, connected via a secure SSH tunnel
to Ollama running qwen2.5-coder:latest on the Hostinger VPS.

Result: A free, persistent, self-improving AI agent (Hermes) that uses the VPS as its
brain — with zero port exposure, no API costs, and full session memory locally.

---

## Architecture

```
[Linux Mint PC]                          [Hostinger VPS]
  Hermes Agent                              Ollama serve
  localhost:11435  <── SSH Tunnel ──>  localhost:11434
  (forwarded port)                     qwen2.5-coder:latest
```

Hermes on Mint talks to `http://localhost:11435` (its local end of the tunnel),
which forwards all traffic securely to Ollama on the VPS at port 11434.
No port is exposed to the public internet.

---

## PART 1 — VPS SIDE SETUP
### (Run these steps while SSH'd into the Hostinger VPS)

### Step 1.1 — Verify Ollama is running and qwen2.5-coder:latest is present

```bash
# Confirm Ollama service status
systemctl is-active ollama

# List available models
ollama list

# If qwen2.5-coder:latest is missing, pull it
ollama pull qwen2.5-coder:latest

# Confirm Ollama is bound to localhost only (required for tunnel security)
ss -tlnp | grep 11434
```

Expected output for last command: `127.0.0.1:11434` — NOT `0.0.0.0:11434`.

If Ollama is bound to 0.0.0.0, lock it down:
```bash
sudo systemctl edit ollama
```
Add these lines:
```ini
[Service]
Environment="OLLAMA_HOST=127.0.0.1:11434"
```
Then:
```bash
sudo systemctl daemon-reload && sudo systemctl restart ollama
```

### Step 1.2 — Tune Ollama for persistent remote use

```bash
sudo systemctl edit ollama
```
Add/confirm these environment variables:
```ini
[Service]
Environment="OLLAMA_HOST=127.0.0.1:11434"
Environment="OLLAMA_KEEP_ALIVE=-1"
Environment="OLLAMA_NUM_PARALLEL=1"
```

- `OLLAMA_KEEP_ALIVE=-1` keeps qwen2.5-coder loaded in RAM permanently (no reload delay per request)
- `OLLAMA_NUM_PARALLEL=1` safe default for CPU-only VPS

```bash
sudo systemctl daemon-reload && sudo systemctl restart ollama

# Verify model responds
curl -s http://localhost:11434/api/generate \
  -d '{"model":"qwen2.5-coder:latest","prompt":"Reply with: tunnel works","stream":false}' \
  | python3 -m json.tool
```

Confirm you see a `response` field in the JSON output before continuing.

### Step 1.3 — Confirm SSH user and key auth is working

```bash
whoami   # note this username — needed for tunnel command on Mint side
```

VPS SSH must use key-based auth (no password prompts) for the tunnel to be persistent.
If your key is already set up from Claude Code sessions, this is already done.

---

## PART 2 — LINUX MINT SIDE SETUP
### (Run these steps on the local Linux Mint machine)

### Step 2.1 — Test SSH connection to VPS

```bash
ssh -o ConnectTimeout=10 YOUR_VPS_USER@YOUR_VPS_IP "ollama list"
```

Replace `YOUR_VPS_USER` and `YOUR_VPS_IP` with actual values.
You should see the Ollama model list without a password prompt.

If it asks for a password, set up key auth first:
```bash
ssh-keygen -t ed25519 -C "mint-to-vps"
ssh-copy-id YOUR_VPS_USER@YOUR_VPS_IP
```

### Step 2.2 — Create the persistent SSH tunnel

This tunnel forwards local port 11435 → VPS port 11434 (Ollama API).

**Option A — One-shot tunnel (for testing):**
```bash
ssh -N -L 11435:localhost:11434 YOUR_VPS_USER@YOUR_VPS_IP
```
Leave this terminal open. Test with:
```bash
curl http://localhost:11435/api/tags
```
You should see qwen2.5-coder:latest in the response. If yes, tunnel works.

**Option B — Persistent systemd tunnel (production, auto-reconnects):**

Create the service file:
```bash
sudo nano /etc/systemd/system/ollama-vps-tunnel.service
```

Paste:
```ini
[Unit]
Description=SSH Tunnel to VPS Ollama
After=network.target
Wants=network-online.target

[Service]
User=YOUR_LOCAL_MINT_USERNAME
ExecStart=/usr/bin/ssh \
  -N \
  -o ServerAliveInterval=30 \
  -o ServerAliveCountMax=3 \
  -o ExitOnForwardFailure=yes \
  -o StrictHostKeyChecking=no \
  -L 11435:localhost:11434 \
  YOUR_VPS_USER@YOUR_VPS_IP
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Replace `YOUR_LOCAL_MINT_USERNAME`, `YOUR_VPS_USER`, `YOUR_VPS_IP` with real values.

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable ollama-vps-tunnel
sudo systemctl start ollama-vps-tunnel
sudo systemctl status ollama-vps-tunnel
```

Verify tunnel is live:
```bash
curl -s http://localhost:11435/api/tags | python3 -m json.tool
```

### Step 2.3 — Install Hermes Agent on Linux Mint

```bash
# Install via the Ollama integration (recommended)
curl -fsSL https://nous.sh/hermes/install.sh | bash

# OR if the above fails, install via pip
pip install hermes-agent --break-system-packages
```

If neither works, check the official install at: https://docs.ollama.com/integrations/hermes

### Step 2.4 — Configure Hermes to use VPS Ollama via tunnel

When Hermes starts and asks for configuration:

- **LLM Provider:** Ollama (or Custom / OpenAI-compatible)
- **Endpoint / Base URL:** `http://localhost:11435/v1`
- **Model:** `qwen2.5-coder:latest`
- **API Key:** `ollama` (dummy value — Ollama doesn't require a real key)

If Hermes stores config in a file, find and edit it:
```bash
find ~/.hermes ~/.config/hermes -name "*.json" -o -name "*.yaml" 2>/dev/null | head -10
```

Set the Ollama base URL to `http://localhost:11435` (NOT 11434 — that's the VPS side).

### Step 2.5 — First run validation

```bash
hermes
```

At the Hermes prompt, test:
```
> What model are you using and where is it running?
> Write a PHP function that validates a South African ID number
```

The second prompt will confirm qwen2.5-coder is actually doing the work (it should produce
clean PHP with relevant comments).

---

## PART 3 — VALIDATION CHECKLIST

Run through each item. Report ✅ or ❌:

```bash
# 1. Ollama running on VPS (run on VPS)
systemctl is-active ollama

# 2. qwen2.5-coder:latest loaded (run on VPS)
ollama list | grep qwen2.5-coder

# 3. Tunnel service running on Mint
systemctl is-active ollama-vps-tunnel

# 4. Tunnel forwarding works (run on Mint)
curl -s http://localhost:11435/api/tags | grep qwen2.5-coder

# 5. Hermes connects and responds
hermes --version
```

---

## PART 4 — TUNING & GOTCHAS

### Response speed expectations
qwen2.5-coder:7b on a CPU-only VPS: ~10–40 tokens/second depending on load.
For coding tasks this is fine. For long generation (full files), expect 30–90 seconds.

### If the tunnel drops
The systemd service auto-restarts after 10 seconds. Check logs with:
```bash
journalctl -u ollama-vps-tunnel -f
```

### If Hermes can't find the model
Ollama's OpenAI-compatible endpoint is at `/v1` — make sure Hermes is pointing to:
`http://localhost:11435/v1` not just `http://localhost:11435`

### Keep the model warm on VPS
If you notice cold-start delays, confirm `OLLAMA_KEEP_ALIVE=-1` is set on the VPS.
Check with: `curl http://localhost:11434/api/ps` (run on VPS — should show qwen2.5-coder loaded)

### Memory usage on VPS (CPU-only)
qwen2.5-coder:latest (7B Q4) uses ~5–6GB RAM. Check VPS headroom:
```bash
free -h    # run on VPS
```
If less than 2GB free after model loads, switch to: `qwen2.5-coder:7b-instruct-q4_K_M`

---

## PART 5 — OPTIONAL: HERMES GATEWAY (Telegram/Discord)

Once confirmed working, Hermes can be connected to Telegram or Discord so you can
chat with your VPS-powered agent from your phone.

When Hermes prompts for a gateway, select Telegram and provide a bot token from
@BotFather on Telegram. This gives you mobile access to the full Hermes+qwen2.5-coder
stack without needing to open a terminal.

---

## Quick Reference

| Component        | Location         | Address                          |
|-----------------|------------------|----------------------------------|
| Ollama API      | Hostinger VPS    | localhost:11434 (VPS-local only) |
| SSH Tunnel      | Linux Mint       | localhost:11435 → VPS:11434      |
| Hermes Agent    | Linux Mint       | CLI / terminal                   |
| Model           | Hostinger VPS    | qwen2.5-coder:latest             |
