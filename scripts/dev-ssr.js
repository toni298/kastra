import { spawn, spawnSync } from 'node:child_process'
import { existsSync, watch } from 'node:fs'
import { resolve } from 'node:path'
import process from 'node:process'

const root = resolve(import.meta.dirname, '..')
const bundlePath = resolve(root, 'bootstrap/ssr/ssr.js')
const vitePath = resolve(root, 'node_modules/vite/bin/vite.js')
const nodeCommand = process.execPath
let ssrServer = null
let buildWatcher = null
let shuttingDown = false
let restartingServer = false
let restartTimer = null

const runBuild = () => {
  const result = spawnSync(nodeCommand, [vitePath, 'build', '--ssr', '--logLevel', 'warn'], {
    cwd: root,
    stdio: 'inherit',
  })

  if (result.status !== 0) {
    throw new Error('Build SSR awal gagal.')
  }

  console.log('[ssr] Bundle awal siap.')
}

const stopExistingServer = () => {
  spawnSync('php', ['artisan', 'inertia:stop-ssr', '--silent'], {
    cwd: root,
    stdio: 'ignore',
  })
}

const startServer = () => {
  if (!existsSync(bundlePath)) return

  ssrServer = spawn(nodeCommand, [bundlePath], {
    cwd: root,
    stdio: 'inherit',
  })

  ssrServer.on('exit', (code) => {
    ssrServer = null

    if (!shuttingDown && !restartingServer && code !== 0) {
      console.error('[ssr] Server berhenti. Menunggu rebuild berikutnya.')
    }
  })
}

const restartServer = () => {
  clearTimeout(restartTimer)
  restartTimer = setTimeout(() => {
    if (ssrServer) {
      restartingServer = true
      ssrServer.once('exit', () => {
        restartingServer = false
        if (!shuttingDown) startServer()
      })
      ssrServer.kill()
      return
    }

    startServer()
  }, 150)
}

const shutdown = () => {
  if (shuttingDown) return

  shuttingDown = true
  clearTimeout(restartTimer)
  buildWatcher?.kill()
  ssrServer?.kill()
  stopExistingServer()
  process.exit(0)
}

runBuild()
stopExistingServer()
startServer()

const watcher = watch(resolve(root, 'bootstrap/ssr'), (_, filename) => {
  if (filename?.replaceAll('\\', '/') === 'ssr.js') restartServer()
})

buildWatcher = spawn(
  nodeCommand,
  [vitePath, 'build', '--ssr', '--watch', '--emptyOutDir=false', '--logLevel', 'warn'],
  {
    cwd: root,
    stdio: 'inherit',
  }
)

buildWatcher.on('exit', (code) => {
  if (!shuttingDown && code !== 0) {
    console.error('[ssr] Watch build berhenti dengan error.')
    shutdown()
  }
})

process.on('SIGINT', shutdown)
process.on('SIGTERM', shutdown)
process.on('exit', () => watcher.close())
