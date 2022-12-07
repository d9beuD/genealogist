export interface UserBase {
  email: string;
  firstname: string;
  lastname: string;
}

export interface User extends UserBase {
  id: number | null;
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
