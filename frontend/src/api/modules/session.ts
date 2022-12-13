import type { loginForm, User, UserWithPassword } from "../types";
import instance from "../instance";

export default {
  login: (form: loginForm): Promise<User> => {
    return instance.post("/login", form);
  },

  register: (form: UserWithPassword): Promise<User> => {
    return instance.post("/registration", form);
  },

  logout: (): Promise<User | null> => {
    return instance.get("/logout");
  },

  getContent: (): Promise<User> => {
    return instance.get("/session");
  },
};
