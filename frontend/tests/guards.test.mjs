import assert from 'node:assert/strict'

const creerStockage = () => {
  const d = new Map()
  return {
    getItem: (k) => (d.has(k) ? d.get(k) : null),
    setItem: (k, v) => d.set(k, String(v)),
    removeItem: (k) => d.delete(k),
    clear: () => d.clear()
  }
}
globalThis.localStorage = creerStockage()
globalThis.sessionStorage = creerStockage()

const { resolveNavigation } = await import('../src/router/guards.js')
const { isAuthenticated, getToken } = await import('../src/auth.js')

const dashboard = { name: 'dashboard', path: '/dashboard', meta: { requiresAuth: true } }
const login = { name: 'login', path: '/login', meta: { requiresAuth: false } }

const cas = []
const verifier = (nom, fn) => { fn(); cas.push(nom) }

localStorage.clear(); sessionStorage.clear()
verifier('deconnecte : /dashboard bloque et redirige vers login', () => {
  assert.equal(isAuthenticated(), false)
  assert.deepEqual(resolveNavigation(dashboard), { name: 'login' })
})

verifier('deconnecte : /login reste accessible (aucune boucle)', () => {
  assert.equal(resolveNavigation(login), true)
})

localStorage.setItem('token', 'jeton-factice-local')
verifier('connecte via localStorage : /dashboard autorise', () => {
  assert.equal(isAuthenticated(), true)
  assert.equal(resolveNavigation(dashboard), true)
})

localStorage.clear()
sessionStorage.setItem('token', 'jeton-factice-session')
verifier('connecte via sessionStorage : /dashboard autorise', () => {
  assert.equal(getToken(), 'jeton-factice-session')
  assert.equal(resolveNavigation(dashboard), true)
})

sessionStorage.clear()
verifier('token supprime : /dashboard de nouveau bloque', () => {
  assert.deepEqual(resolveNavigation(dashboard), { name: 'login' })
})

verifier('route sans meta : autorisee', () => {
  assert.equal(resolveNavigation({ path: '/' }), true)
})

console.log(cas.map((c, i) => `  OK ${i + 1}. ${c}`).join('\n'))
console.log(`\n${cas.length}/6 verifications passees`)
