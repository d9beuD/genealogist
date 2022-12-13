export interface entity {
  id: number;
}

export interface UserBase {
  email: string;
  firstname: string;
  lastname: string;
}

export interface User extends entity, UserBase {
  roles: string[];
  userIdentifier: string;
}

export interface UserWithPassword extends UserBase {
  password: string;
}

export interface loginForm {
  username: string;
  password: string;
}

export interface treeForm {
  name: string;
}

export interface Tree extends entity, treeForm {}

export interface APIList<T = any> {
  data: T[];
  count: number;
  limit: number;
  offset: number;
}

export interface JsonDate {
  date: string;
  timezone_type: string;
  timezone: string;
}

export interface personForm {
  firstname: string | null;
  lastname: string | null;
  birthName: string | null;
  birthDate: Date | JsonDate | null;
  deathDate: Date | JsonDate | null;
  description: string | null;
  picture: string | null;
  isBirthDateKnown: boolean;
  isDeathDateKnown: boolean;
  isBirthDateCertain: boolean;
  isDeathDateCertain: boolean;
  importantDates: unknown[];
}

export interface Person extends entity, personForm {
  parents: Person[];
  children: Person[];
}
