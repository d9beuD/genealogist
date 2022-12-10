import type { AxiosResponse } from "axios";
import instance from "../instance";
import type { Tree, treeForm } from "../types";

export default {
  add: (form: treeForm): Promise<AxiosResponse<Tree>> => {
    return instance.post("/tree", form);
  },
};
