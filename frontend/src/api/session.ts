import type { AxiosResponse } from "axios";
import type { loginForm, User, UserWithPassword } from "./types";
import instance from "./instance";

export default {
  login: (form: loginForm): Promise<AxiosResponse<User>> => {
    return instance.post("/login", form);
  },
};
