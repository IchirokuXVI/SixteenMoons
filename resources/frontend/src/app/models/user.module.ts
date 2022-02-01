export interface User {
  id: string;
  username: string;
  email: string;
  email_verified_at: Date;
  password: string;
  privacy_level: number;
  name: string;
  age: number;
  created_at?: Date;
  updated_at?: Date;
}
