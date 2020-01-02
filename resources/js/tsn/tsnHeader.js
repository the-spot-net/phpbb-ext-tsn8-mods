import { MDCTopAppBar } from '@material/top-app-bar/component';
import { MDCDrawer } from '@material/drawer/component';
import tsnPlugin from './tsnPlugin';

/**
 * Handles the Top App Bar
 */
export default class tsnHeader extends tsnPlugin {
  constructor({
    container,
    options = {},
    name = ''
  } = {}) {
    super({ container, options, name });
    this.constructListeners();
  }

  static init({
    $context = tsnHeader.$document || $(document)
  } = {}) {
    $context.find(tsnHeader.selectors.mdcTopAppBar).tsnHeader();
  }

  constructDynamicProperties() {
    super.constructDynamicProperties();

    this.MDCDrawer = this.MDCDrawer || new MDCDrawer(document.querySelector(tsnHeader.selectors.mdcDrawer));
    this.MDCTopAppBar = this.MDCTopAppBar || new MDCTopAppBar(document.querySelector(tsnHeader.selectors.mdcTopAppBar));

    this.navigationList = this.navigationList || document.querySelector(tsnHeader.selectors.mdcDrawerList);
    this.mainContent = this.mainContent || document.querySelector('main');
  }

  constructListeners() {
    // Updates the drawer's open state to its opposite
    this.MDCTopAppBar.listen(tsnHeader.events.mdcTopAppBar.navClicked, () => {
      this.MDCDrawer.open = !this.MDCDrawer.open;
    });

    // Close the drawer when clicking a list item in it
    this.navigationList.addEventListener('click', () => {
      this.MDCDrawer.open = false;
    });

    // Force-focus the first focusable element in the main content after the drawer is closed
    document.body.addEventListener(tsnHeader.events.mdcDrawer.closed, () => {
      const firstElement = this.mainContent.querySelector('input, button');
      if (firstElement) {
        firstElement.focus();
      }
    });
  }
}

if (!$.fn.tsnHeader) {
  $.fn.tsnHeader = function () {
    return this.each(function () {
      return tsnPlugin.getPluginObject({ pluginName: tsnHeader.pluginName, $elem: $(this) }) || new tsnHeader({ container: this });
    });
  };
}

tsnHeader.events = {
  mdcDrawer: {
    closed: 'MDCDrawer:closed',
    opened: 'MDCDrawer:opened'
  },
  mdcTopAppBar: {
    navClicked: 'MDCTopAppBar:nav'
  }
};
tsnHeader.pluginName = 'tsnHeader';
tsnHeader.selectors = {
  mdcDrawer: '.mdc-drawer',
  mdcDrawerList: '.mdc-drawer .mdc-list',
  mdcTopAppBar: '.mdc-top-app-bar'
};
