import tsnNamespace from './tsnNamespace';

/** Generic FireScope Plugin class for other plugins to inherit */
export default class tsnPlugin {
  /**
   * @param {Object} container
   * @param {string} pluginName
   */
  constructor({
    container,
    options = {},
    name = ''
  } = {}) {
    this.container = container;
    this.options = options;
    this.name = name;

    tsnPlugin.constructStaticProperties();

    this.constructDynamicProperties();

    this.constructPlugin();
  }

  /**
   * Cache some jQuery objects once for all plugins
   * We wait for the first plugin to be instantiated to ensure that the DOM is ready
   */
  static constructStaticProperties() {
    tsnPlugin.$window = tsnPlugin.$window || $(window);
    tsnPlugin.$document = tsnPlugin.$document || $(window.document);
    tsnPlugin.$body = tsnPlugin.$body || $('body');
  }

  /**
   * @param pluginName
   * @param $context
   * @returns {jQuery}
   */
  static getAllPluginContainers({
    pluginName,
    $context = tsnPlugin.$document
  } = {}) {
    return $context.find(`[${tsnPlugin.getKey({ pluginName })}]`);
  }

  /**
   * @param pluginName
   * @param $context
   * @returns {[]}
   */
  static getAllPluginObjects({
    pluginName,
    $context = tsnPlugin.$document
  } = {}) {
    const pluginObjects = [];

    tsnPlugin.getAllPluginContainers({
      pluginName,
      $context
    }).each((i, elem) => {
      pluginObjects.push(tsnPlugin.getPluginObject({
        pluginName,
        $elem: $(elem)
      }));
    });

    return pluginObjects;
  }

  /**
   * @param pluginName
   * @param $context
   * @returns {object|undefined}
   */
  static getFirstPluginObject({
    pluginName,
    $context = tsnPlugin.$document
  } = {}) {
    return tsnPlugin.getPluginObject({
      pluginName,
      $elem: tsnPlugin.getAllPluginContainers({ pluginName, $context })
    });
  }

  static getKey({ pluginName }) {
    return `${tsnPlugin.constants.prefix}-${pluginName}`;
  }

  /**
   * @param pluginName
   * @param $elem
   * @returns {*}
   */
  static getPluginObject({
    pluginName,
    $elem
  } = {}) {
    return $elem.data(tsnPlugin.getKey({ pluginName }));
  }

  /**
   * Returns a jQuery object for the given selector
   * @param {string} selector
   * @param {boolean} searchContainerOnly
   * @returns {Object}
   */
  $elem(selector, searchContainerOnly = false) {
    const salt = searchContainerOnly ? 'local' : 'global';
    const key = selector + salt;

    // check for a cached version of the requested selector
    if (typeof (this.elemCache[key]) === 'undefined' || this.elemCache[key].length === 0) {
      // cache the object
      if (searchContainerOnly) {
        this.elemCache[key] = this.$container.find(selector);
      } else {
        this.elemCache[key] = $(selector);
      }
    }

    return this.elemCache[key];
  }

  constructDynamicProperties() {
    this.$container = $(this.container);
    this.elemCache = {};
    this.key = tsnPlugin.getKey({ pluginName: this.name });
    this.namespace = tsnNamespace({ objectName: this.name });
  }

  constructPlugin() {
    this.$container
      .data(this.key, this)
      .attr(this.key, '');
  }

  /**
   * Continuously check for the passed dependencies - stop and run the callback once all have loaded
   * @param dependencies
   * @param callback
   */
  dependencyDelay({
    dependencies = {},
    callback = () => {}
  } = {}) {
    let ready = true;

    $.each(dependencies, (i, dependency) => {
      if (!ready || typeof this.$elem(dependency.selector, true).data(dependency.pluginName) === 'undefined') {
        ready = false;
      }
    });

    if (ready) {
      callback();
    } else {
      this.dependencyDelayTimeout = setTimeout(() => {
        this.dependencyDelay({
          dependencies,
          callback
        });
      }, 100);
    }
  }
}

tsnPlugin.constants = {
  prefix: 'tsnPlugin'
};
