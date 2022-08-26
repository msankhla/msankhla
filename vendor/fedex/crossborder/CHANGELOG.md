## [1.0.7]
### Added
- Implemented Partial Pack Notification.

### Changed
- Changed downloading Pack Notification documents.
- Changed list of shipping service level ids for Merchant Control account.

## [1.0.6.3]
### Added
- Implemented FedEx Cross Border shipping method logs.

## [1.0.6.2]
### Fixed
- Fixed problem with displaying domestic shipping methods which were related to some specific shipping methods that return an array for titles.

## [1.0.6.1]
### Added
- Implemented custom shipping rates for Merchant Control accounts.
- Implemented functionality to use currency rate received from order notification for order creating process.

### Changed
- Included loss and damage protection cost into estimate shipping cost.
- Changed the logic to warning about unavailable configurable product. Now the configurable product will be available till the customer choose an option.
- Hidden the "Order Confirmation Path" option in configuration settings as this option was deprecated for the Magento module and this callback path now can be configured from FedEx Cross Border Merchant account only.

## [1.0.6]
### Added
- Implemented additional functionality to disallow the native checkout page if the customer already started it and at the same time trying to use international shipping.
- The CHANGELOG file was added.

### Changed
- The logic for the order creating process was changed to implement a possibility to use "Customizable Options" and custom product types.
- The logic for tax calculation was changed.

## [1.0.5.3]
### Fixed
- Fixed problem with displaying information for API Scheduler.

## [1.0.5.2]
### Added
- Welcome Mat:
    - Implemented the possibility of turning off Welcome Mat.
    - Implemented the possibility to not show Welcome Mat if the first-time site opened.
    - Missed flags for some countries was added.

### Changed
- The IP length was changed for IPv6.
