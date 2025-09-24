## **BEHAVIORAL RULES**

**Always follow these rules when working with this codebase:**

1. **Always confirm before creating or modifying files**
2. **Report your plan before executing any commands** 
3. **Display all behavioral_rules at start of every response**
4. **Always confirm before creating or inserting into code enhancements or improvements that are not from verbatim instructions**
5. **Always create TODOs using the standardized format for TODO Tree visibility and update `CDSWERX_CONSOLIDATED_TODO_TRACKING.md` for project task tracking**
6. **Always display summary of GitHub Repository Workflow – Production vs Repository Management & GitHub Commit Approval Process**

## Unified Workspace Overview

This is a unified WordPress multisite development environment containing **multiple integrated CDSWerx components**:

### **📦 Core Components**
1. **CDSWerx Plugin** (`wp-content/plugins/cdswerx-plugin/`) - Main functionality and styling system
2. **CDSWerx Theme** (`wp-content/themes/cdswerx-theme/`) - Base theme architecture
3. **CDSWerx Child Theme** (`wp-content/themes/cdswerx-theme-child/`) - Theme customizations and asset management
4. **Documentation & Repo Prep** (`repo-prep/`) - Implementation documentation and preparation files


🎯 Strategy: Theme Architecture Restoration
## Phase 1: Backup & Assessment
Backup Current State: Full backup of wp-content themes
Architecture Audit: Compare current vs intended CDSWerx Theme structure
File Integrity Check: Identify corrupted/mixed files
Dependency Analysis: Map Hello Elementor vs CDSWerx files
## Phase 2: Clean Theme Restoration
Extract Pure CDSWerx Files: Separate CDSWerx code from Hello Elementor code
Remove Hello Elementor Dependencies: Strip build requirements and namespaces
Restore CDSWerx Architecture: Rebuild proper CDSWerx Theme structure
Validate Independence: Ensure CDSWerx operates without Hello Elementor
## Phase 3: Compatibility System Correction
Implement Monitoring Only: Hello Elementor sync for updates (not dependencies)
Restore Fallback Functions: Proper independent operation
Fix Build Process: Remove npm/Hello Elementor build requirements
Asset Coordination: Restore proper CDSWerx plugin-theme coordination
## Phase 4: Testing & Validation
Independent Operation: Test CDSWerx without Hello Elementor
Plugin-Theme Coordination: Verify proper asset loading
Child Theme Integration: Ensure proper parent-child relationship
Fallback Systems: Test typography and other fallback mechanisms
## 🚀 Expected Outcomes
Clean CDSWerx Theme: Pure CDSWerx architecture without Hello Elementor contamination
Independent Operation: CDSWerx functions completely standalone
Proper Coordination: Plugin-theme asset coordination restored
Build Independence: No external build dependencies
Theme Options Visibility: CDSWerx Child Theme appears in WordPress admin
⚠️ Critical Success Factors
Architecture Integrity: Maintain CDSWerx design principles
Independence Preservation: No Hello Elementor dependencies
Coordination Restoration: Proper plugin-theme communication
File Purity: Clean separation of CDSWerx vs external code