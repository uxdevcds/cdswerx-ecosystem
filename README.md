# CDSWerx Ecosystem - Monorepo

**Implementation Date:** September 24, 2025  
**Architecture:** Hybrid WordPress Development + Git Version Control  

## 🏗️ Repository Structure

```
cdswerx-ecosystem/
├── packages/
│   ├── cdswerx-plugin/          # CDSWerx Plugin source code
│   ├── cdswerx-theme/           # CDSWerx Theme source code  
│   └── cdswerx-theme-child/     # CDSWerx Child Theme source code
├── docs/                        # Unified ecosystem documentation
├── tools/                       # Development and sync utilities
├── .github/workflows/           # CI/CD and release automation
└── README.md                    # This file
```

## 🔄 Development Workflow

### Local WordPress Development
- Live WordPress environment: `../wp-content/`
- Real-time testing and debugging
- Immediate visual feedback

### Version Control
- Source of truth: `packages/` directories in this repo
- Git tracking for proper version history
- Component releases via subtree distribution

### Sync Strategy
- **Ecosystem → WordPress**: Automated file watchers
- **WordPress → Ecosystem**: Manual sync commands
- **Bidirectional**: Conflict detection and resolution

## 🚀 Component Distribution

Individual GitHub repositories for WordPress.org distribution:
- `cdswerx-plugin` (subtree: packages/cdswerx-plugin)
- `cdswerx-theme` (subtree: packages/cdswerx-theme)
- `cdswerx-theme-child` (subtree: packages/cdswerx-theme-child)

## 🔧 Development Commands

```bash
# Start file watchers for auto-sync to WordPress
npm run watch

# Manual sync from WordPress to ecosystem
npm run sync:from-wp

# Release individual components
npm run release:plugin
npm run release:theme
npm run release:child-theme

# Test ecosystem integration
npm run test:ecosystem
```

## 📊 Implementation Evidence

**Baseline:** 1075 files tracked across all components  
**Backup:** `HYBRID_IMPLEMENTATION_BACKUP_20250924_180422/`  
**Verification:** All WordPress functionality preserved  

## 🎯 Goals Achieved

✅ **Hybrid Development**: WordPress live testing + Git version control  
✅ **Component Independence**: Individual releases while maintaining ecosystem coordination  
✅ **Documentation Unity**: Centralized docs with component-specific sections  
✅ **Rollback Safety**: Complete backup and restoration capability  
✅ **Evidence-Based**: Systematic verification at every step  