import Vue from "vue";
import Router from "vue-router";

Vue.use(Router);

export default new Router({
    routes: [
        {
            path: "/",
            component: () => import("@/views/AdminWizard"),

            children: [
                {
                    name: "admin",
                    path: "step1",
                    component: () => import("@/views/AdminBasic")
                }
            ]
        },

        {
            name: "course",
            path: "/course",
            component: () => import("@/views/Course"),
        }
    ]
});