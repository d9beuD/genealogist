import { useSessionStore } from "@/stores/session";
import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
      meta: { auth: true },
    },
    {
      path: "/about",
      name: "about",
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import("../views/AboutView.vue"),
    },
    {
      path: "/login",
      name: "login",
      component: () => import("../views/LoginView.vue"),
    },
    {
      path: "/register",
      name: "registration",
      component: () => import("../views/RegisterView.vue"),
    },
    {
      path: "/trees",
      component: () => import("../views/DefaultView.vue"),
      meta: { auth: true },
      children: [
        {
          path: "new",
          name: "newTree",
          component: () => import("../components/forms/NewTreeForm.vue"),
        },
      ],
    },
  ],
});

router.beforeEach((to) => {
  const session = useSessionStore();

  // Checks if the route requires to be loggedIn or have an admin role
  if (to.matched.some((route) => route.meta.auth || route.meta.admin)) {
    // If so, does the user need to be admin?
    if (to.matched.some((route) => route.meta.admin)) {
      if (!session.isAdmin) {
        return { name: "login" };
      }
    }

    // If the user isn't logged in, redirect this mf to the login form
    if (!session.isLoggedIn) {
      return { name: "login" };
    }
  }
});

export default router;
