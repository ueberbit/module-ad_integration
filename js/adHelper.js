/**
 * @file
 */

function getDeviceType() {
  'use strict';

  var Breakpoints = window.breakpointSettings.Breakpoints;
  var DeviceMapping = window.breakpointSettings.DeviceMapping;

  if (window.innerWidth <= Breakpoints[DeviceMapping.tablet]) {
    return 'smartphone';
  }

  if (window.innerWidth <= Breakpoints[DeviceMapping.desktop]) {
    return 'tablet';
  }

  return 'desktop';
}

window.deviceIsMobile = function () {
  'use strict';

  return (getDeviceType() === 'smartphone');
};

window.deviceIsTablet = function () {
  'use strict';

  return (getDeviceType() === 'tablet');
};

window.deviceIsDesktop = function () {
  'use strict';

  return (getDeviceType() === 'desktop');
};
