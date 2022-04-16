import fs from 'fs-extra';
import chalk from 'chalk';
import { join } from 'path';
import { knex, SNIPPETS_PATH, PAGE_PATH, UTILS_PATH } from './utils.mjs';

/**
 * Main Begin End
 */
await fs.remove(SNIPPETS_PATH);
await loadSnippets();
await knex.destroy();

/**
 * Load Snippets
 */
async function loadSnippets() {
  const snippets = await knex('wpw1_snippets').select('*');
  let count = 0;

  for (let i = 0; i < snippets.length; i++) {
    let { name, code } = snippets[i];
    const functionName = code.match(/function ([^(]+)/);

    if (functionName) {
      const p0 = name.match(/function:\s*(.*)$/i);
      const fnName = functionName[1];

      // replace all absolute URL paths
      code = code.replace(/https:\/\/cwa.cwops.org/gm, '');

      if (p0) {
        await savePage(fnName, code);
        console.log(++count, chalk.red(fnName));
      }
      else {
        await saveUtil(fnName, code);
        console.log(++count, chalk.green(fnName));
      }
    }
  }
}

/**
 * Save Util File
 */
async function saveUtil(name, code) {
  const path = join(PAGE_PATH, name + '.php');

  code = code.replace(/^.*?add_shortcode.*$/gmi, '');
  code = code.trim();

  await fs.outputFile(path, '<?php\n' + code);
}

/**
 * Save Page File
 */
async function savePage(name, code) {
  const path = join(UTILS_PATH, name + '.php');

  code = code.replace(/^.*?add_action.*$/gmi, '');
  code = code.trim();

  await fs.outputFile(path, '<?php\n' + code);
}

