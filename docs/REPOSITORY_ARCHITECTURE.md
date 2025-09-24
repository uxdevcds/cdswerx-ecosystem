# CDSWerx Hybrid Ecosystem Architecture

## Overview

The CDSWerx ecosystem uses a hybrid architecture that combines the benefits of a monorepo for unified development with individual component repositories for distribution and compatibility.

## Repository Structure

| Repository | Purpose | Relationship |
|------------|---------|-------------|
| **`uxdevcds/cdswerx-ecosystem`** | **Monorepo Hub** | Contains all components + coordinates releases |
| **`uxdevcds/cdswerx-plugin`** | **Individual Component** | Receives updates FROM ecosystem via `git subtree` |
| **`uxdevcds/cdswerx-theme`** | **Individual Component** | Receives updates FROM ecosystem via `git subtree` |
| **`uxdevcds/cdswerx-theme-child`** | **Individual Component** | Receives updates FROM ecosystem via `git subtree` |
| **`uxdevcds/cdswerx-dev-environment`** | **WordPress Testing** | Live development environment for real-time testing |

## Workflow Architecture

### Development Flow

1. **Development Happens In**: `cdswerx-ecosystem/packages/`
2. **Testing Happens In**: WordPress environment (with bidirectional sync)
3. **Releases Deploy To**: Individual component repositories via `git subtree push`

### Repository Flow Diagram

```text
cdswerx-ecosystem (MONOREPO)
├── packages/cdswerx-plugin/          ──┐
├── packages/cdswerx-theme/           ──┼── git subtree push ──┐
├── packages/cdswerx-theme-child/     ──┘                     │
├── docs/                                                     │
├── tools/                                                    │
└── package.json (with release scripts)                      │
                                                              ▼
                    INDIVIDUAL REPOSITORIES (MAINTAINED)
                    ├── uxdevcds/cdswerx-plugin      ◄───────┘
                    ├── uxdevcds/cdswerx-theme       ◄───────┐
                    └── uxdevcds/cdswerx-theme-child ◄───────┘
```

## Benefits of Hybrid Architecture

### Why Keep Individual Repositories?

1. **💡 WordPress Plugin/Theme Directory Compatibility**: Individual repos can be submitted to WordPress.org
2. **🔗 Composer Integration**: Individual repos work with Composer package managers  
3. **📦 Distributed Development**: Teams can work on individual components if needed
4. **🏷️ Independent Versioning**: Each component can have its own release tags
5. **📚 Focused Documentation**: Component-specific README files and documentation
6. **🔄 Backward Compatibility**: Existing integrations continue to work

### Why Use Ecosystem Monorepo?

1. **🎯 Unified Development**: Single place for all component development
2. **🔄 Coordinated Releases**: Ensure all components work together
3. **📋 Centralized Documentation**: Comprehensive development guides and architecture docs
4. **🛠️ Shared Tooling**: Common build, test, and deployment scripts
5. **🔗 Cross-Component Integration**: Easy coordination between plugin, theme, and child theme

## Development Commands

### Sync Tools

```bash
# Start development with real-time sync
npm run watch

# Manual sync operations
npm run sync:from-wp      # WordPress → Ecosystem  
npm run sync:to-wp        # Ecosystem → WordPress
```

### Component Releases

```bash
# From cdswerx-ecosystem directory:
npm run release:plugin      # Pushes packages/cdswerx-plugin → uxdevcds/cdswerx-plugin
npm run release:theme       # Pushes packages/cdswerx-theme → uxdevcds/cdswerx-theme  
npm run release:child-theme # Pushes packages/cdswerx-theme-child → uxdevcds/cdswerx-theme-child
```

## Git Remote Configuration

The ecosystem repository maintains connections to all component repositories:

```bash
# Primary ecosystem repository
origin                 https://github.com/uxdevcds/cdswerx-ecosystem.git

# Component repository remotes for subtree deployment
plugin-origin          https://github.com/uxdevcds/cdswerx-plugin.git
theme-origin           https://github.com/uxdevcds/cdswerx-theme.git
child-theme-origin     https://github.com/uxdevcds/cdswerx-theme-child.git
```

## File Structure

```
cdswerx-ecosystem/
├── packages/
│   ├── cdswerx-plugin/           # Source of truth for plugin development
│   ├── cdswerx-theme/            # Source of truth for theme development
│   └── cdswerx-theme-child/      # Source of truth for child theme development
├── docs/
│   ├── REPOSITORY_ARCHITECTURE.md
│   ├── HYBRID_WORKFLOW_MIGRATION_GUIDE.md
│   └── ecosystem/                # Organized documentation
├── tools/
│   ├── ecosystem-to-wp.js        # Sync ecosystem → WordPress
│   ├── wp-to-ecosystem.js        # Sync WordPress → ecosystem
│   └── watch-and-sync.js         # Real-time file watching and sync
├── package.json                  # NPM scripts and dependencies
└── README.md                     # Project overview and quick start
```

## WordPress Integration

The ecosystem maintains bidirectional sync with the WordPress development environment:

- **WordPress Location**: `/wp-content/themes/` and `/wp-content/plugins/`
- **Sync Direction**: Ecosystem ↔ WordPress (bidirectional)
- **Real-time Sync**: File watchers automatically sync changes during development
- **Manual Sync**: Available for quick fixes made directly in WordPress admin

## Migration Notes

This hybrid architecture was implemented to solve the "royal mess" of scattered development while maintaining compatibility with existing WordPress workflows and distribution channels.

### Previous State

- Individual repositories with potential path conflicts
- Scattered documentation across multiple locations
- Manual coordination between components
- Inconsistent versioning and release processes

### Current State

- Unified development in ecosystem monorepo
- Automated deployment to individual repositories
- Centralized documentation and tooling
- Coordinated versioning across all components
- Maintained backward compatibility with existing integrations

## Best Practices

1. **Always develop in the ecosystem**: Use `cdswerx-ecosystem/packages/` as your primary development location
2. **Use sync tools**: Let the automated sync handle file coordination
3. **Release via subtree**: Use npm scripts for deploying to individual repositories
4. **Maintain documentation**: Keep both ecosystem and component-specific docs updated
5. **Test in WordPress**: Use the development environment for real-world testing

## Troubleshooting

For common issues and troubleshooting guides, see:
- `/docs/ecosystem/troubleshooting/`
- Individual component documentation in each package
- WordPress development environment logs and debug information