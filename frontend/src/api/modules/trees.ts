import type { AxiosResponse } from "axios";
import instance from "../instance";
import type { APIList, Tree, treeForm } from "../types";

export default {
  add: (form: treeForm): Promise<AxiosResponse<Tree>> => {
    return instance.post("/tree", form);
  },

  list: (): Promise<AxiosResponse<APIList<Tree>>> => {
    return instance.get("/tree");
  },

  get: (id: number | string): Promise<AxiosResponse<Tree>> => {
    return instance.get(`/tree/${id}`);
  },

  edit: (tree: Tree): Promise<AxiosResponse<Tree>> => {
    return instance.put(`/tree/${tree.id}`, tree);
  },

  delete: (tree: Tree): Promise<AxiosResponse> => {
    return instance.delete(`/tree/${tree.id}`);
  },
};
