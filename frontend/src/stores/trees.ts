import api from "@/api";
import type { Tree, Person } from "@/api/types";
import { defineStore } from "pinia";
import { computed, reactive, ref } from "vue";

export const useTreeStore = defineStore("trees", () => {
  const members = reactive<Person[]>(
    JSON.parse(localStorage.getItem("app.content.tree.members") ?? "[]")
  );
  const tree = ref<Tree | null>(
    JSON.parse(localStorage.getItem("app.content.tree.id") ?? "null")
  );

  const orderedMembers = computed(() => {
    return [...members].sort((a, b) => {
      return `${a.lastname} ${a.firstname}`.localeCompare(
        `${b.lastname} ${b.firstname}`
      );
    });
  });

  const groupedMembers = computed(() => {
    const list: { [key: string]: Person[] } = {};

    orderedMembers.value.forEach((person) => {
      if (person.lastname === null || person.lastname.length === 0) {
        return;
      }

      const letter = person.lastname.toUpperCase().charAt(0);

      // If the letter does not exists as key object
      if (!Object.keys(list).find((key) => key === letter)) {
        list[letter] = [];
      }

      list[letter].push(Object.assign({}, person));
    });

    return list;
  });

  function saveState() {
    localStorage.setItem("app.content.tree.members", JSON.stringify(members));
    localStorage.setItem("app.content.tree.id", JSON.stringify(tree.value));
  }

  function loadTree(treeId: number) {
    api.trees.get(treeId).then((response) => {
      tree.value = response;
    });
    api.trees.getMembers(treeId).then((response) => {
      members.splice(0, members.length);
      members.push(...response);
    });
    saveState();
  }

  function getMember(memberId: string) {
    const member = members.find((item) => {
      return item.id.toString() === memberId;
    });

    if (member) {
      return Object.assign({}, member);
    }

    return member;
  }

  function addMember(member: Person) {
    members.push(member);
    saveState();
  }

  function updateMember(member: Person) {
    const oldMember = members.find((item) => item.id === member.id);

    if (oldMember) {
      Object.assign(oldMember, member);
    }
    saveState();
  }

  function removeMember(member: Person) {
    const index = members.findIndex((item) => item.id === member.id);

    if (index >= 0) {
      members.splice(index, 1);
    }
    saveState();
  }

  return {
    tree,
    orderedMembers,
    groupedMembers,
    loadTree,
    getMember,
    addMember,
    updateMember,
    removeMember,
  };
});
