import type { User } from "@/api/types";
import { defineStore } from "pinia";
import { computed, ref } from "vue";

export const useSessionStore = defineStore("session", () => {
  const user = ref(
    JSON.parse(
      sessionStorage.getItem("app.session.user") || "null"
    ) as User | null
  );

  const isAdmin = computed(
    () => user.value?.roles.some((role) => role === "ROLE_ADMIN") || false
  );

  function setUser(payload: User) {
    user.value = payload;
    sessionStorage.setItem("app.session.user", JSON.stringify(payload));
  }

  return { user, isAdmin, setUser };
});
