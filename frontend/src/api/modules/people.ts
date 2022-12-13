import instance from "../instance";
import type { APIList, Person, personForm } from "../types";

export default {
  getTreeMembers: (treeId: number): Promise<APIList<Person>> => {
    return instance.get(`/tree/${treeId}/members`);
  },

  getMember: (memberId: number): Promise<Person> => {
    return instance.get(`/person/${memberId}`);
  },

  add: (treeId: number, form: personForm): Promise<Person> => {
    return instance.post(`/tree/${treeId}/members`, form);
  },

  update: (memberId: number, form: Person): Promise<Person> => {
    return instance.put(`/person/${memberId}`, form);
  },
};
