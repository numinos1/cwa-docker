import fs from 'fs-extra';
import Knex from 'knex';
import dotenv from 'dotenv';
import { dirname, join, resolve } from 'path';
import { fileURLToPath } from 'url';

export const ROOT_PATH = dirname(fileURLToPath(import.meta.url));
export const ENV_PATH = resolve(join(ROOT_PATH, '..', '..', '.env'));

export const env = dotenv.parse(fs.readFileSync(ENV_PATH));

export const SNIPPETS_PATH = resolve(join(ROOT_PATH, '..', 'snippets'));
export const PAGE_PATH = join(SNIPPETS_PATH, 'admin');
export const UTILS_PATH = join(SNIPPETS_PATH, 'utils');

export const knex = Knex({
  client: 'mysql',
  connection: {
    host: '127.0.0.1',
    port: 3074,
    user: env.db_user,
    password: env.db_password,
    database: env.db_name,
  }
});
