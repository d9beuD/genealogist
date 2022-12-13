import instance from "../instance";
import type { APIList, Person } from "../types";

export default {
  getTreeMembers: (treeId: number): Promise<APIList<Person>> => {
    return instance.get(`/tree/${treeId}/members`);
  },
};
