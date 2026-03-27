const { execFileSync } = require('node:child_process')

function getBaseURL() {
  return process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8080'
}

function getAppURL(path = '/') {
  return new URL(path, getBaseURL()).toString()
}

function getConsoleInvocation(args) {
  if (process.env.PLAYWRIGHT_EXEC_IN_DOCKER === '0') {
    return {
      command: args[0],
      args: args.slice(1),
    }
  }

  return {
    command: 'docker',
    args: ['exec', process.env.PLAYWRIGHT_APP_CONTAINER || 'app.catroweb', ...args],
  }
}

function runConsole(args) {
  const invocation = getConsoleInvocation(args)
  execFileSync(invocation.command, invocation.args, {
    cwd: process.cwd(),
    stdio: 'inherit',
  })
}

function preparePlaywrightEnvironment() {
  runPhpScript(['tests/Playwright/support/prepare-web-general.php'])
}

function seedDataset(dataset) {
  runPhpScript(['tests/Playwright/support/seed-web-general.php', dataset])
}

function runPhpScript(args) {
  runConsole(['php', ...args])
}

async function waitForApp(baseURL, timeoutMs = 120000) {
  const startedAt = Date.now()
  let lastError
  const requestTimeoutMs = Math.min(timeoutMs, 5000)

  while (Date.now() - startedAt < timeoutMs) {
    try {
      const response = await fetch(baseURL, {
        redirect: 'manual',
        signal: AbortSignal.timeout(requestTimeoutMs),
      })
      if (response.ok || [301, 302, 303, 307, 308].includes(response.status)) {
        return
      }

      lastError = new Error(`Unexpected response status ${response.status}`)
    } catch (error) {
      lastError = error
    }

    await new Promise((resolve) => setTimeout(resolve, 1000))
  }

  throw new Error(
    `Timed out waiting for Catroweb at ${baseURL}: ${lastError instanceof Error ? lastError.message : String(lastError)}`,
  )
}

module.exports = {
  getAppURL,
  getBaseURL,
  preparePlaywrightEnvironment,
  seedDataset,
  waitForApp,
}
