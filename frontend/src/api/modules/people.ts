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

  delete: (memberId: number): Promise<Person | null> => {
    return instance.delete(`/person/${memberId}`);
  },

  setPicture: (memberId: number, picture: File): Promise<Person> => {
    return instance.postMultipart(`/person/${memberId}/picture`, {
      name: "picture",
      data: picture,
    });
  },

  removePicture: (memberId: number): Promise<Person> => {
    return instance.delete(`person/${memberId}/picture`);
  },
};
