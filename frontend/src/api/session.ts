import type { AxiosResponse } from "axios";
import type { User } from "./types";
import instance from "./instance";

export default {
  login: (form: {
    username: string;
    password: string;
  }): Promise<AxiosResponse<User>> => {
    return instance.post("/login", form);
  },
};
