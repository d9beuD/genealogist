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
  trees: unknown[];
  userIdentifier: string;
}

export interface UserWithPassword extends UserBase {
  password: string;
}

export interface loginForm {
  username: string;
  password: string;
}
