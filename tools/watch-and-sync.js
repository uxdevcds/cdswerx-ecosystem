const chokidar = require('chokidar');
const path = require('path');
const chalk = require('chalk');
const EcosystemToWordPressSync = require('./ecosystem-to-wp');

/**
 * File Watcher and Auto-Sync Tool
 * 
 * Watches ecosystem packages for changes and automatically syncs to WordPress
 * providing real-time development experience.
 */

const CONFIG = {
  WATCH_PATHS: [
    'packages/cdswerx-plugin/**/*',
    'packages/cdswerx-theme/**/*', 
    'packages/cdswerx-theme-child/**/*'
  ],
  IGNORED_PATTERNS: [
    '**/node_modules/**',
    '**/.git/**',
    '**/.DS_Store',
    '**/Thumbs.db',
    '**/*.tmp',
    '**/*.log'
  ],
  DEBOUNCE_DELAY: 1000 // 1 second debounce
};

class WatchAndSync {
  constructor() {
    this.logPrefix = chalk.cyan('[WATCHER]');
    this.syncTool = new EcosystemToWordPressSync();
    this.pendingSync = null;
    this.isWatching = false;
    this.changeCount = 0;
  }

  start() {
    console.log(this.logPrefix, 'Starting file watcher...');
    console.log(this.logPrefix, `Watching paths:`, CONFIG.WATCH_PATHS);
    console.log(this.logPrefix, `Debounce delay: ${CONFIG.DEBOUNCE_DELAY}ms`);
    console.log(this.logPrefix, chalk.green('Ready for real-time sync! 🚀'));
    console.log(this.logPrefix, chalk.dim('Press Ctrl+C to stop watching'));
    
    const watcher = chokidar.watch(CONFIG.WATCH_PATHS, {
      ignored: CONFIG.IGNORED_PATTERNS,
      persistent: true,
      ignoreInitial: true
    });

    watcher.on('ready', () => {
      this.isWatching = true;
      console.log(this.logPrefix, chalk.green('File watcher is ready and monitoring changes...'));
    });

    watcher.on('add', (filePath) => this.handleFileChange('added', filePath));
    watcher.on('change', (filePath) => this.handleFileChange('changed', filePath));
    watcher.on('unlink', (filePath) => this.handleFileChange('removed', filePath));

    watcher.on('error', (error) => {
      console.error(this.logPrefix, chalk.red('Watcher error:'), error);
    });

    // Handle graceful shutdown
    process.on('SIGINT', () => {
      console.log(this.logPrefix, chalk.yellow('\nShutting down file watcher...'));
      watcher.close().then(() => {
        console.log(this.logPrefix, chalk.green('File watcher stopped. Goodbye! 👋'));
        process.exit(0);
      });
    });

    return watcher;
  }

  handleFileChange(changeType, filePath) {
    if (!this.isWatching) return;

    this.changeCount++;
    const relativePath = path.relative(process.cwd(), filePath);
    const component = this.getComponentName(filePath);
    
    console.log(
      this.logPrefix,
      chalk.blue(`[${changeType.toUpperCase()}]`),
      chalk.dim(`${component}:`),
      path.basename(filePath)
    );

    // Debounce sync operations
    if (this.pendingSync) {
      clearTimeout(this.pendingSync);
    }

    this.pendingSync = setTimeout(() => {
      this.performSync(component);
    }, CONFIG.DEBOUNCE_DELAY);
  }

  getComponentName(filePath) {
    if (filePath.includes('cdswerx-plugin')) return 'Plugin';
    if (filePath.includes('cdswerx-theme-child')) return 'Child Theme';
    if (filePath.includes('cdswerx-theme')) return 'Theme';
    return 'Unknown';
  }

  async performSync(component) {
    console.log(this.logPrefix, chalk.yellow(`Syncing ${component} to WordPress...`));
    
    const startTime = Date.now();
    const success = await this.syncTool.sync();
    const duration = Date.now() - startTime;
    
    if (success) {
      console.log(
        this.logPrefix,
        chalk.green(`✅ ${component} synced successfully`),
        chalk.dim(`(${duration}ms)`)
      );
    } else {
      console.log(
        this.logPrefix,
        chalk.red(`❌ ${component} sync failed`),
        chalk.dim(`(${duration}ms)`)
      );
    }
    
    console.log(this.logPrefix, chalk.dim(`Total changes processed: ${this.changeCount}`));
    console.log(this.logPrefix, chalk.green('Ready for more changes... 👀'));
  }
}

// Execute if run directly
if (require.main === module) {
  const watcher = new WatchAndSync();
  watcher.start();
}

module.exports = WatchAndSync;