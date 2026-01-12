# Joomla Development Setup for RA-Walks

This document provides setup instructions for developing the **RA-Walks** Joomla component in a local development environment.

## Prerequisites

### System Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher (or MariaDB equivalent)
- **Joomla**: 4.x or higher
- **Composer**: For dependency management
- **Git**: For version control

### Required Dependencies

**RA-Walks is dependent on RA-Tools component.** Ensure RA-Tools is installed and enabled before installing RA-Walks.

## Installation Steps

### 1. Install Base Joomla Instance

Set up a clean Joomla 4.x installation in your local development environment.

```bash
# Example using Docker or local server
# Follow Joomla official installation guide
```

### 2. Install RA-Tools Component (Required Dependency)

Before installing RA-Walks, you must first install the RA-Tools component:

```bash
# Clone or download RA-Tools
git clone https://github.com/CharlieBigley/ra-tools.git

# Follow RA-Tools installation instructions
# Ensure it's properly installed and enabled in Joomla Administration
```

### 3. Clone RA-Walks Repository

```bash
# Clone the repository into your development directory
git clone https://github.com/CharlieBigley/ra-walks.git
```

### 4. Install RA-Walks Component

#### Option A: Manual Installation via Admin Panel
1. Go to Joomla Administrator Panel
2. Navigate to **System > Install Extensions**
3. Choose the installation method:
   - **Upload Package File**: Select the component package (or packaged from source)
   - **Install from URL**: Use the Git repository URL
   - **Install from Folder**: Select the cloned directory

#### Option B: Command Line Installation
```bash
# Copy the component to the Joomla extensions directory
cp -r com_ra_walks /path/to/joomla/administrator/components/com_ra_walks
cp -r mod_ra_walks /path/to/joomla/modules/mod_ra_walks
cp -r plg_ramblerswalks /path/to/joomla/plugins/system/plg_ramblerswalks
```

### 5. Database Setup

The RA-Walks component will automatically create necessary database tables during installation. Ensure your database is accessible and has sufficient permissions.

#### Expected Tables
- `#__ra_walks`: Main walks data
- `#__ra_walks_follow`: User followers for walks
- Related tables for areas, groups, and supporting data

### 6. Configure Component

1. Go to **System > Extensions > Manage Extensions**
2. Find **RA-Walks** and verify it's **Enabled**
3. Navigate to **System > Plugins** and enable:
   - **System - RA-Walks Ramblers Walks Plugin**
4. Configure component parameters at **Home > Components > RA-Walks**

## Development Workflow

### Directory Structure
```
com_ra_walks/
├── administrator/          # Admin-facing code
│   ├── src/               # PHP source files
│   │   ├── Controller/    # Admin controllers
│   │   ├── Extension/     # Component extension class
│   │   ├── Field/         # Custom form fields
│   │   ├── Model/         # Data models
│   │   ├── Service/       # Service providers
│   │   ├── Table/         # Database table classes
│   │   └── View/          # Admin views
│   ├── tmpl/              # Admin templates
│   ├── forms/             # Form XML definitions
│   ├── language/          # Language strings
│   └── sql/               # Database schema
├── site/                  # Frontend-facing code
│   ├── src/              # PHP source files
│   │   ├── Controller/   # Frontend controllers
│   │   ├── Dispatcher/   # Request dispatcher
│   │   ├── Helpers/      # Helper classes
│   │   ├── Model/        # Data models
│   │   ├── Service/      # Service providers
│   │   └── View/         # Frontend views
│   ├── tmpl/             # Frontend templates
│   ├── forms/            # Form XML definitions
│   ├── language/         # Language strings
│   └── css/              # Frontend stylesheets
├── media/                 # Media assets
│   ├── css/
│   └── js/
└── com_ra_walks.xml       # Component manifest

mod_ra_walks/             # Module for displaying walks
plg_ramblerswalks/        # System plugin
```

### Editing Component Code

1. **Backend Components**: Edit files in `com_ra_walks/administrator/src/`
2. **Frontend Components**: Edit files in `com_ra_walks/site/src/`
3. **Templates**: Edit files in respective `tmpl/` directories
4. **Language Files**: Update strings in `language/` directories

### Building/Packaging

To create an installable package:

```bash
# Create a ZIP file containing the component
cd /path/to/development/directory
zip -r com_ra_walks.zip com_ra_walks/
zip -r mod_ra_walks.zip mod_ra_walks/
zip -r plg_ramblerswalks.zip plg_ramblerswalks/
```

## Testing

### Local Testing
1. Make changes to source files
2. Clear Joomla cache: **System > Clear Cache**
3. Test functionality in both Administrator and Frontend
4. Check Joomla error logs: `logs/` directory

### Database Migration Testing
- Test on fresh database install
- Test on upgrade from previous versions
- Verify SQL scripts in `administrator/sql/`

## Version Management

### Git Workflow
```bash
# Create a feature branch
git checkout -b feature/your-feature-name

# Commit changes
git add .
git commit -m "your commit message"

# Push to repository
git push origin feature/your-feature-name

# Create Pull Request on GitHub
```

### Version Format
Component versions follow semantic versioning: `MAJOR.MINOR.PATCH`
- Update `@version` tags in PHP file docblocks
- Update version in `com_ra_walks.xml` manifest

## Dependencies

### Required Extensions
- **com_ra_tools**: Core utilities and helpers
  - Must be installed and enabled
  - Provides helper classes and database utilities

### Optional Extensions
- **com_ra_wf**: Walks Follow (for user follow features)
- Other Ramblers components as needed

## Common Development Tasks

### Add a New Report
1. Create new controller method in `site/src/Controller/ReportsController.php`
2. Add corresponding view/template in `site/src/View/`
3. Update menu item configuration if needed

### Add a New Database Field
1. Update database schema in `administrator/sql/`
2. Create migration script for existing installations
3. Update model classes to handle new field

### Add Language Strings
1. Edit language file: `language/en-GB/com_ra_walks.ini`
2. Reference in code: `Text::_('COM_RA_WALKS_KEY')`

## Debugging

### Enable Debug Mode
In Joomla `configuration.php`:
```php
public $debug = true;
public $error_reporting = 'development';
```

### View Logs
Check logs in: `logs/error.log`

### Database Debugging
Use Joomla's built-in SQL debug viewer in Administrator > System > Debug

## Resources

- [Joomla 4 Developer Documentation](https://docs.joomla.org)
- [Joomla Component Development Guide](https://docs.joomla.org/J4.x:Developing_an_MVC_Component)
- [RA-Tools Repository](https://github.com/CharlieBigley/ra-tools)

## Support

For issues or questions:
1. Check existing GitHub Issues
2. Review component documentation
3. Consult Joomla community forums
4. Contact component maintainers

## License

GNU General Public License v2 or later
