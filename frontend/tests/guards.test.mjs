// Verification executable de la garde de navigation (SCRUM-13), du
// comportement de redirection (SCRUM-14) et de la gestion de session (SCRUM-15).
//   docker compose exec frontend npm run test:guards
import assert from 'node:assert/strict'

// Stub minimal des stockages du navigateur (Node ne les fournit pas).
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

const { resolveNavigation, resolveRedirection } = await import('../src/router/guards.js')
const { isAuthenticated, getToken, getUser, saveSession, clearSession } =
  await import('../src/auth.js')

const dashboard = { name: 'dashboard', path: '/dashboard', fullPath: '/dashboard', meta: { requiresAuth: true } }
const login = { name: 'login', path: '/login', fullPath: '/login', meta: { requiresAuth: false } }

const cas = []
const verifier = (nom, fn) => { fn(); cas.push(nom) }

// ---------------------------------------------------------------- SCRUM-13
localStorage.clear(); sessionStorage.clear()
verifier('SCRUM-13 deconnecte : /dashboard est bloque', () => {
  assert.equal(isAuthenticated(), false)
  assert.equal(resolveNavigation(dashboard).name, 'login')
})

verifier('SCRUM-13 deconnecte : /login reste accessible (aucune boucle)', () => {
  assert.equal(resolveNavigation(login), true)
})

localStorage.setItem('token', 'jeton-factice-local')
verifier('SCRUM-13 connecte via localStorage : /dashboard autorise', () => {
  assert.equal(isAuthenticated(), true)
  assert.equal(resolveNavigation(dashboard), true)
})

localStorage.clear()
sessionStorage.setItem('token', 'jeton-factice-session')
verifier('SCRUM-13 connecte via sessionStorage : /dashboard autorise', () => {
  assert.equal(getToken(), 'jeton-factice-session')
  assert.equal(resolveNavigation(dashboard), true)
})

sessionStorage.clear()
verifier('SCRUM-13 token supprime : /dashboard de nouveau bloque', () => {
  assert.equal(resolveNavigation(dashboard).name, 'login')
})

verifier('SCRUM-13 route sans meta : autorisee', () => {
  assert.equal(resolveNavigation({ path: '/' }), true)
})

// ---------------------------------------------------------------- SCRUM-14
localStorage.clear(); sessionStorage.clear()
verifier('SCRUM-14 la destination voulue est memorisee dans ?redirect', () => {
  const decision = resolveNavigation({ ...dashboard, fullPath: '/dashboard?onglet=stats' })
  assert.deepEqual(decision, {
    name: 'login',
    query: { redirect: '/dashboard?onglet=stats' }
  })
})

localStorage.setItem('token', 'jeton-factice-local')
verifier('SCRUM-14 connecte : /login renvoie vers /dashboard', () => {
  assert.deepEqual(resolveNavigation(login), { name: 'dashboard' })
})

verifier('SCRUM-14 apres connexion : retour sur la page demandee', () => {
  assert.equal(resolveRedirection('/dashboard?onglet=stats'), '/dashboard?onglet=stats')
})

verifier('SCRUM-14 sans ?redirect : destination par defaut /dashboard', () => {
  assert.equal(resolveRedirection(undefined), '/dashboard')
})

verifier('SCRUM-14 ?redirect=/login est ignore (pas de boucle)', () => {
  assert.equal(resolveRedirection('/login'), '/dashboard')
  assert.equal(resolveRedirection('/login?redirect=/login'), '/dashboard')
})

verifier('SCRUM-14 une redirection externe est refusee', () => {
  assert.equal(resolveRedirection('https://site-externe.example'), '/dashboard')
  assert.equal(resolveRedirection('//site-externe.example'), '/dashboard')
})

verifier('SCRUM-14 une valeur non textuelle est refusee', () => {
  assert.equal(resolveRedirection(['/a', '/b']), '/dashboard')
  assert.equal(resolveRedirection(null), '/dashboard')
})

// ---------------------------------------------------------------- SCRUM-15
const utilisateur = { id: 1, name: 'Sara Bennani', email: 'sara@example.com' }

localStorage.clear(); sessionStorage.clear()
verifier('SCRUM-15 connexion avec "Se souvenir de moi" : session persistante', () => {
  saveSession({ token: 'jeton-a', user: utilisateur, remember: true })
  assert.equal(localStorage.getItem('token'), 'jeton-a')
  assert.equal(sessionStorage.getItem('token'), null)
  assert.deepEqual(getUser(), utilisateur)
  assert.equal(isAuthenticated(), true)
})

verifier('SCRUM-15 connexion sans "Se souvenir de moi" : session de l onglet', () => {
  clearSession()
  saveSession({ token: 'jeton-b', user: utilisateur, remember: false })
  assert.equal(sessionStorage.getItem('token'), 'jeton-b')
  assert.equal(localStorage.getItem('token'), null)
  assert.equal(getToken(), 'jeton-b')
})

verifier('SCRUM-15 une nouvelle connexion ne laisse pas l ancien token', () => {
  clearSession()
  saveSession({ token: 'ancien', user: utilisateur, remember: true })
  saveSession({ token: 'nouveau', user: utilisateur, remember: false })
  assert.equal(localStorage.getItem('token'), null)
  assert.equal(getToken(), 'nouveau')
})

verifier('SCRUM-15 etat restaure apres rafraichissement', () => {
  clearSession()
  saveSession({ token: 'jeton-c', user: utilisateur, remember: true })
  // Un rafraichissement recharge les modules mais pas le stockage : on relit.
  assert.equal(isAuthenticated(), true)
  assert.equal(getUser().name, 'Sara Bennani')
  assert.equal(resolveNavigation(dashboard), true)
})

verifier('SCRUM-15 deconnexion : les deux stockages sont vides', () => {
  clearSession()
  saveSession({ token: 'jeton-d', user: utilisateur, remember: true })
  sessionStorage.setItem('user', JSON.stringify(utilisateur)) // residu eventuel
  clearSession()
  assert.equal(getToken(), null)
  assert.equal(getUser(), null)
  assert.equal(localStorage.getItem('user'), null)
  assert.equal(sessionStorage.getItem('user'), null)
})

verifier('SCRUM-15 apres deconnexion : /dashboard est de nouveau bloque', () => {
  assert.equal(resolveNavigation(dashboard).name, 'login')
})

verifier('SCRUM-15 donnee utilisateur illisible : session effacee, pas de plantage', () => {
  clearSession()
  localStorage.setItem('token', 'jeton-e')
  localStorage.setItem('user', '{ceci-n-est-pas-du-json')
  assert.equal(getUser(), null)
  assert.equal(getToken(), null)
})

console.log(cas.map((c, i) => `  OK ${String(i + 1).padStart(2)}. ${c}`).join('\n'))
console.log(`\n${cas.length}/${cas.length} verifications passees`)
