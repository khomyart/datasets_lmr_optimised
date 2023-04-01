const routes = [
  {
    path: "/login",
    name: "login",
    component: () => import("layouts/LoginLayout.vue"),
  },

  {
    path: "/",
    meta: {
      isAuthNeeded: true,
    },
    name: "all",
    component: () => import("layouts/MainLayout.vue"),
    // redirect: "/all",
    // children: [
    //   {
    //     name: "all",
    //   },
    // ],
  },

  {
    path: "/:catchAll(.*)*",
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
