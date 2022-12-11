import type { AxiosResponse } from "axios";
import instance from "../instance";
import type { APIList, Person } from "../types";

export default {
  getTreeMembers: (treeId: number): Promise<AxiosResponse<APIList<Person>>> => {
    return instance.get(`/tree/${treeId}/members`);
  },
};
