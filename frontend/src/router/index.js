import { createRouter, createWebHistory } from 'vue-router'

import { routes } from './routes.js'
import { resolveNavigation } from './guards'
import OrdonnancesView from '../views/Ordonnances/OrdonnancesView.vue'

// SCRUM-532 - Ce module ne fait plus que brancher la table de routes sur
// l'historique du navigateur et y installer la garde. La table elle-meme vit
// dans routes.js, ou elle peut etre verifiee sans navigateur.

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(resolveNavigation)

export default router
