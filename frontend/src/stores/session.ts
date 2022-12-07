import type { User } from "@/api/types";
import { defineStore } from "pinia";
import { computed, ref } from "vue";

export const useSessionStore = defineStore("session", () => {
  const user = ref<User | null>(
    JSON.parse(sessionStorage.getItem("app.session.user") || "null")
  );
  const minPasswordLength = ref(8);

  const isAdmin = computed(
    () => user.value?.roles.some((role) => role === "ROLE_ADMIN") || false
  );

  function setUser(payload: User) {
    user.value = payload;
    sessionStorage.setItem("app.session.user", JSON.stringify(payload));
  }

  return { user, minPasswordLength, isAdmin, setUser };
});
