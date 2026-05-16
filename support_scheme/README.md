# Community Support Scheme Module for UNACMS

A comprehensive community-based fundraising module designed for South African municipalities, where residents can support those in need through their local community spaces.

## Features

- **Campaign Management**: Create, manage, and track fundraising campaigns
- **Donation System**: Easy-to-use donation interface with multiple payment options
- **Category Filtering**: Organize campaigns by type (Food, Education, Health, Housing, etc.)
- **Progress Tracking**: Visual progress bars showing fundraising goals
- **Space Integration**: Each community (Space) can have its own campaigns
- **Anonymous Donations**: Option for donors to remain anonymous
- **Email Notifications**: Automatic notifications for campaigns and donations

## Requirements

- UNACMS v15.0.x
- PHP 7.4+
- MySQL 5.7+

## Installation

1. Extract the zip file to your UNACMS modules directory:
   ```
   /var/www/html/modules/sa/support_scheme/
   ```

2. Set proper permissions:
   ```bash
   chmod -R 755 /var/www/html/modules/sa/support_scheme
   ```

3. Go to Admin → Studio → Modules

4. Find "Community Support Scheme" and click **Install**

## Usage

### Creating a Campaign

1. Navigate to Community Support → Create Campaign
2. Fill in the campaign details:
   - Title
   - Description
   - Goal Amount (in ZAR)
   - Category
   - End Date (optional)
3. Submit for approval

### Making a Donation

1. Browse available campaigns
2. Click on a campaign to view details
3. Enter donation amount and optional message
4. Choose to donate anonymously or publicly
5. Complete payment

## Categories

- Food & Nutrition
- Education
- Healthcare
- Housing
- Utilities
- Clothing
- Transport
- Funeral Costs
- Small Business
- Emergency Relief
- General Support

## Configuration

Access module settings in Studio → Community Support Scheme:

- Enable/disable campaign creation
- Enable/disable donations
- Set minimum donation amount
- Set maximum campaigns per user
- Configure currency (default: ZAR)
- Auto-approve campaigns

## License

MIT License

## Support

For support, please visit: https://unacms.com/
