const fs = require('fs-extra');
const path = require('path');
const chalk = require('chalk');

/**
 * WordPress to CDSWerx Ecosystem Sync Tool
 * 
 * Syncs changes from WordPress development environment back to ecosystem packages
 * with conflict detection and validation.
 */

const CONFIG = {
  WORDPRESS_ROOT: path.resolve(__dirname, '../..'),
  ECOSYSTEM_ROOT: path.resolve(__dirname, '..'),
  SYNC_MAPPINGS: {
    'wp-content/plugins/cdswerx-plugin': 'packages/cdswerx-plugin',
    'wp-content/themes/cdswerx-theme': 'packages/cdswerx-theme',
    'wp-content/themes/cdswerx-theme-child': 'packages/cdswerx-theme-child'
  },
  EXCLUDED_FILES: [
    '.DS_Store',
    '.git',
    'node_modules', 
    '.vscode',
    'Thumbs.db'
  ]
};

class WordPressToEcosystemSync {
  constructor() {
    this.logPrefix = chalk.magenta('[WP→ECOSYSTEM]');
    this.errors = [];
    this.warnings = [];
    this.synced = [];
    this.conflicts = [];
  }

  async sync() {
    console.log(this.logPrefix, 'Starting WordPress to ecosystem sync...');
    console.log(this.logPrefix, `Timestamp: ${new Date().toISOString()}`);
    
    try {
      await this.validateEnvironment();
      await this.detectConflicts();
      
      if (this.conflicts.length > 0) {
        console.log(chalk.yellow('⚠️  Conflicts detected. Please resolve before syncing.'));
        this.displayConflicts();
        return false;
      }
      
      await this.performSync();
      this.displayResults();
      
      if (this.errors.length === 0) {
        console.log(chalk.green('✅ WordPress sync completed successfully!'));
        return true;
      } else {
        console.log(chalk.red('❌ WordPress sync completed with errors.'));
        return false;
      }
    } catch (error) {
      console.error(chalk.red('💥 WordPress sync failed:'), error.message);
      return false;
    }
  }

  async validateEnvironment() {
    console.log(this.logPrefix, 'Validating environment...');
    
    // Check WordPress components exist
    for (const [wpPath] of Object.entries(CONFIG.SYNC_MAPPINGS)) {
      const fullPath = path.join(CONFIG.WORDPRESS_ROOT, wpPath);
      if (!await fs.pathExists(fullPath)) {
        throw new Error(`WordPress component not found: ${wpPath}`);
      }
    }
    
    // Check ecosystem packages exist  
    for (const [, ecosystemPath] of Object.entries(CONFIG.SYNC_MAPPINGS)) {
      const fullPath = path.join(CONFIG.ECOSYSTEM_ROOT, ecosystemPath);
      if (!await fs.pathExists(fullPath)) {
        throw new Error(`Ecosystem package not found: ${ecosystemPath}`);
      }
    }
    
    console.log(this.logPrefix, chalk.green('Environment validation passed'));
  }

  async detectConflicts() {
    console.log(this.logPrefix, 'Detecting potential conflicts...');
    
    // For now, we'll implement basic timestamp checking
    // In a full implementation, you'd want more sophisticated conflict detection
    
    for (const [wpPath, ecosystemPath] of Object.entries(CONFIG.SYNC_MAPPINGS)) {
      await this.checkComponentConflicts(wpPath, ecosystemPath);
    }
    
    if (this.conflicts.length === 0) {
      console.log(this.logPrefix, chalk.green('No conflicts detected'));
    }
  }

  async checkComponentConflicts(wpPath, ecosystemPath) {
    const wpFullPath = path.join(CONFIG.WORDPRESS_ROOT, wpPath);
    const ecosystemFullPath = path.join(CONFIG.ECOSYSTEM_ROOT, ecosystemPath);
    
    try {
      const wpStat = await fs.stat(wpFullPath);
      const ecosystemStat = await fs.stat(ecosystemFullPath);
      
      // Check if WordPress version is older than ecosystem version
      if (wpStat.mtime < ecosystemStat.mtime) {
        this.warnings.push(`WordPress ${wpPath} appears older than ecosystem version`);
      }
      
    } catch (error) {
      this.errors.push(`Could not check conflict for ${wpPath}: ${error.message}`);
    }
  }

  async performSync() {
    console.log(this.logPrefix, 'Performing file synchronization...');
    
    for (const [wpPath, ecosystemPath] of Object.entries(CONFIG.SYNC_MAPPINGS)) {
      await this.syncComponent(wpPath, ecosystemPath);
    }
  }

  async syncComponent(wpPath, ecosystemPath) {
    const sourcePath = path.join(CONFIG.WORDPRESS_ROOT, wpPath);
    const targetPath = path.join(CONFIG.ECOSYSTEM_ROOT, ecosystemPath);
    
    console.log(this.logPrefix, `Syncing: ${wpPath} → ${ecosystemPath}`);
    
    try {
      // Ensure target directory exists
      await fs.ensureDir(path.dirname(targetPath));
      
      // Copy with file filtering
      await fs.copy(sourcePath, targetPath, {
        overwrite: true,
        filter: (src) => this.shouldIncludeFile(src)
      });
      
      this.synced.push(`${wpPath} → ${ecosystemPath}`);
      console.log(this.logPrefix, chalk.green(`✅ ${path.basename(wpPath)} synced`));
      
    } catch (error) {
      this.errors.push(`Failed to sync ${wpPath}: ${error.message}`);
      console.log(this.logPrefix, chalk.red(`❌ ${path.basename(wpPath)} failed`));
    }
  }

  shouldIncludeFile(filePath) {
    const basename = path.basename(filePath);
    return !CONFIG.EXCLUDED_FILES.includes(basename);
  }

  displayConflicts() {
    if (this.conflicts.length > 0) {
      console.log(chalk.yellow('\n⚡ Conflicts detected:'));
      this.conflicts.forEach(conflict => console.log(`  - ${conflict}`));
      console.log(chalk.yellow('\nPlease resolve conflicts before syncing.'));
    }
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
  const sync = new WordPressToEcosystemSync();
  sync.sync().then(success => {
    process.exit(success ? 0 : 1);
  });
}

module.exports = WordPressToEcosystemSync;