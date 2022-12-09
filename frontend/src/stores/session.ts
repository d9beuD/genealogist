import type { User } from "@/api/types";
import { defineStore } from "pinia";
import { computed, reactive, ref } from "vue";

export const useSessionStore = defineStore("session", () => {
  const user = ref<User | null>(
    JSON.parse(sessionStorage.getItem("app.session.user") || "null")
  );
  const minPasswordLength = ref(8);

  const isLoggedIn = computed(() => {
    return user.value !== null;
  });
  const isAdmin = computed(() => {
    if (isLoggedIn.value) {
      user.value!.roles.some((role) => role === "ROLE_ADMIN");
    }
    return false;
  });

  function setUser(payload: User) {
    console.log(JSON.stringify(payload));

    user.value = reactive<User>(payload);
    sessionStorage.setItem("app.session.user", JSON.stringify(payload));
  }

  function logout() {
    user.value = null;
    sessionStorage.removeItem("app.session.user");
  }

  return { user, minPasswordLength, isAdmin, isLoggedIn, setUser, logout };
});
