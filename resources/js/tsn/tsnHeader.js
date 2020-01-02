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
    this.leftNavigationDrawer = this.leftNavigationDrawer || new MDCDrawer(document.querySelector(tsnHeader.selectors.mdcDrawer));
    this.topAppBar = this.topAppBar || new MDCTopAppBar(document.querySelector(tsnHeader.selectors.mdcTopAppBar));
  }

  constructListeners() {
    // Updates the drawer's open state to its opposite
    this.topAppBar.listen('MDCTopAppBar:nav', () => {
      this.leftNavigationDrawer.open = !this.leftNavigationDrawer.open;
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

tsnHeader.pluginName = 'tsnHeader';
tsnHeader.selectors = {
  mdcDrawer: '.mdc-drawer',
  mdcTopAppBar: '.mdc-top-app-bar'
};
