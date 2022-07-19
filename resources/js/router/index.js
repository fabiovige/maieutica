import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'kids',
            component: () => import('../Pages/Kids/Index')
        },
        {
            path: '/',
            name: 'users',
            component: () => import('../Pages/Users/Index')
        }
    ]
})

export default router
