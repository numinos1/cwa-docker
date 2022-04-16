import chalk from 'chalk';
import { knex } from './utils.mjs';
const tables = await getTables();

tables.forEach(table => {
  if ((/^cwa_/).test(table.name)) {
    if (!(/2$/).test(table.name)) {
      console.log();
      console.log(chalk.blue(table.name), table.total);
      Object.keys(table.props).forEach(prop => {
        const type = table.props[prop].Type.replace(/\(.*?\)/, '');
        
        console.log('-', chalk.green(prop), type);
      })
    }
  }
});

//console.log(tables);

knex.destroy();

/**
 * Get Tables
 */
async function getTables() {
  const results = await knex.raw('show tables');
  const rows = results[0];
  const tables = [];

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const name = row.Tables_in_cwacwops_wp540;
    const [props, total] = await Promise.all([
      getTableProps(name),
      getRowCount(name)
    ]);

    tables.push({ name, total, props });
  }
  return tables;
}

/**
 * Get Table
 */
async function getTableProps(table) {
  const results = await knex.raw(`describe ${table}`);
  const rows = results[0];
  const out = {};

  for (let i = 0; i < rows.length; i++) {
    const { Field, ...props } = rows[i];
    out[Field] = props;
  }
  return out;
}

/**
 * Get Row Count
 */
async function getRowCount(table) {
  const results = await knex.raw(`select count(*) as total from ${table}`);

  return results[0][0].total;
}