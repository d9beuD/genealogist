import instance from "../instance";
import type { APIList, Person, Tree, treeForm } from "../types";

export default {
  add: (form: treeForm): Promise<Tree> => {
    return instance.post("/tree", form);
  },

  list: (): Promise<APIList<Tree>> => {
    return instance.get("/tree");
  },

  get: (id: number | string): Promise<Tree> => {
    return instance.get(`/tree/${id}`);
  },

  edit: (tree: Tree): Promise<Tree> => {
    return instance.put(`/tree/${tree.id}`, tree);
  },

  delete: (tree: Tree): Promise<Tree | null> => {
    return instance.delete(`/tree/${tree.id}`);
  },

  getMembers: (treeId: number): Promise<Person[]> => {
    return instance.get(`/tree/${treeId}/members`);
  },
};
