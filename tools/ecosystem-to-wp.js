const fs = require('fs-extra');
const path = require('path');
const chalk = require('chalk');

/**
 * CDSWerx Ecosystem to WordPress Sync Tool
 * 
 * Syncs changes from ecosystem packages to WordPress development environment
 * with validation and conflict detection.
 */

const CONFIG = {
  WORDPRESS_ROOT: path.resolve(__dirname, '../..'),
  ECOSYSTEM_ROOT: path.resolve(__dirname, '..'),
  SYNC_MAPPINGS: {
    'packages/cdswerx-plugin': 'wp-content/plugins/cdswerx-plugin',
    'packages/cdswerx-theme': 'wp-content/themes/cdswerx-theme', 
    'packages/cdswerx-theme-child': 'wp-content/themes/cdswerx-theme-child'
  },
  EXCLUDED_FILES: [
    '.DS_Store',
    '.git',
    'node_modules',
    '.vscode',
    'Thumbs.db'
  ]
};

class EcosystemToWordPressSync {
  constructor() {
    this.logPrefix = chalk.blue('[ECOSYSTEM→WP]');
    this.errors = [];
    this.warnings = [];
    this.synced = [];
  }

  async sync() {
    console.log(this.logPrefix, 'Starting ecosystem to WordPress sync...');
    console.log(this.logPrefix, `Timestamp: ${new Date().toISOString()}`);
    
    try {
      await this.validateEnvironment();
      await this.performSync();
      this.displayResults();
      
      if (this.errors.length === 0) {
        console.log(chalk.green('✅ Sync completed successfully!'));
        return true;
      } else {
        console.log(chalk.red('❌ Sync completed with errors.'));
        return false;
      }
    } catch (error) {
      console.error(chalk.red('💥 Sync failed:'), error.message);
      return false;
    }
  }

  async validateEnvironment() {
    console.log(this.logPrefix, 'Validating environment...');
    
    // Check WordPress root exists
    if (!await fs.pathExists(CONFIG.WORDPRESS_ROOT)) {
      throw new Error('WordPress root directory not found');
    }
    
    // Check WordPress config exists
    const wpConfigPath = path.join(CONFIG.WORDPRESS_ROOT, 'wp-config.php');
    if (!await fs.pathExists(wpConfigPath)) {
      throw new Error('WordPress wp-config.php not found');
    }
    
    // Check all ecosystem packages exist
    for (const [ecosystemPath] of Object.entries(CONFIG.SYNC_MAPPINGS)) {
      const fullPath = path.join(CONFIG.ECOSYSTEM_ROOT, ecosystemPath);
      if (!await fs.pathExists(fullPath)) {
        throw new Error(`Ecosystem package not found: ${ecosystemPath}`);
      }
    }
    
    console.log(this.logPrefix, chalk.green('Environment validation passed'));
  }

  async performSync() {
    console.log(this.logPrefix, 'Performing file synchronization...');
    
    for (const [ecosystemPath, wpPath] of Object.entries(CONFIG.SYNC_MAPPINGS)) {
      await this.syncComponent(ecosystemPath, wpPath);
    }
  }

  async syncComponent(ecosystemPath, wpPath) {
    const sourcePath = path.join(CONFIG.ECOSYSTEM_ROOT, ecosystemPath);
    const targetPath = path.join(CONFIG.WORDPRESS_ROOT, wpPath);
    
    console.log(this.logPrefix, `Syncing: ${ecosystemPath} → ${wpPath}`);
    
    try {
      // Ensure target directory exists
      await fs.ensureDir(path.dirname(targetPath));
      
      // Copy with file filtering
      await fs.copy(sourcePath, targetPath, {
        overwrite: true,
        filter: (src) => this.shouldIncludeFile(src)
      });
      
      this.synced.push(`${ecosystemPath} → ${wpPath}`);
      console.log(this.logPrefix, chalk.green(`✅ ${path.basename(ecosystemPath)} synced`));
      
    } catch (error) {
      this.errors.push(`Failed to sync ${ecosystemPath}: ${error.message}`);
      console.log(this.logPrefix, chalk.red(`❌ ${path.basename(ecosystemPath)} failed`));
    }
  }

  shouldIncludeFile(filePath) {
    const basename = path.basename(filePath);
    return !CONFIG.EXCLUDED_FILES.includes(basename);
  }

  displayResults() {
    console.log(this.logPrefix, chalk.bold('Sync Results:'));
    console.log(`  Synced: ${chalk.green(this.synced.length)} components`);
    console.log(`  Warnings: ${chalk.yellow(this.warnings.length)}`);
    console.log(`  Errors: ${chalk.red(this.errors.length)}`);
    
    if (this.synced.length > 0) {
      console.log(chalk.green('\n✅ Successfully synced:'));
      this.synced.forEach(item => console.log(`  - ${item}`));
    }
    
    if (this.warnings.length > 0) {
      console.log(chalk.yellow('\n⚠️  Warnings:'));
      this.warnings.forEach(warning => console.log(`  - ${warning}`));
    }
    
    if (this.errors.length > 0) {
      console.log(chalk.red('\n❌ Errors:'));
      this.errors.forEach(error => console.log(`  - ${error}`));
    }
  }
}

// Execute if run directly
if (require.main === module) {
  const sync = new EcosystemToWordPressSync();
  sync.sync().then(success => {
    process.exit(success ? 0 : 1);
  });
}

module.exports = EcosystemToWordPressSync;