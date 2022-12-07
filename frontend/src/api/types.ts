export interface User {
  id: number | null;
  email: string;
  roles: string[];
  firstname: string;
  lastname: string;
  trees: unknown[];
  userIdentifier: string;
}

export interface UserWithPassword extends User {
  password: string;
}
