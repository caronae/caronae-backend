/*
 * Copyright 2013 Small Batch, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
// Copyright 2006 The Closure Library Authors. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS-IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * @fileoverview Bootstrap for the Google JS Library (Closure).
 *
 * In uncompiled mode base.js will write out Closure's deps file, unless the
 * global <code>CLOSURE_NO_DEPS</code> is set to true.  This allows projects to
 * include their own deps file(s) from different locations.
 *
 *
 * @provideGoog
 */


/**
 * @define {boolean} Overridden to true by the compiler when --closure_pass
 *     or --mark_as_compiled is specified.
 */
var COMPILED = false;


/**
 * Base namespace for the Closure library.  Checks to see goog is
 * already defined in the current scope before assigning to prevent
 * clobbering if base.js is loaded more than once.
 *
 * @const
 */
var goog = goog || {}; // Identifies this file as the Closure base.


/**
 * Reference to the global context.  In most cases this will be 'window'.
 */
goog.global = this;


/**
 * @define {boolean} DEBUG is provided as a convenience so that debugging code
 * that should not be included in a production js_binary can be easily stripped
 * by specifying --define goog.DEBUG=false to the JSCompiler. For example, most
 * toString() methods should be declared inside an "if (goog.DEBUG)" conditional
 * because they are generally used for debugging purposes and it is difficult
 * for the JSCompiler to statically determine whether they are used.
 */
goog.DEBUG = true;


/**
 * @define {string} LOCALE defines the locale being used for compilation. It is
 * used to select locale specific data to be compiled in js binary. BUILD rule
 * can specify this value by "--define goog.LOCALE=<locale_name>" as JSCompiler
 * option.
 *
 * Take into account that the locale code format is important. You should use
 * the canonical Unicode format with hyphen as a delimiter. Language must be
 * lowercase, Language Script - Capitalized, Region - UPPERCASE.
 * There are few examples: pt-BR, en, en-US, sr-Latin-BO, zh-Hans-CN.
 *
 * See more info about locale codes here:
 * http://www.unicode.org/reports/tr35/#Unicode_Language_and_Locale_Identifiers
 *
 * For language codes you should use values defined by ISO 693-1. See it here
 * http://www.w3.org/WAI/ER/IG/ert/iso639.htm. There is only one exception from
 * this rule: the Hebrew language. For legacy reasons the old code (iw) should
 * be used instead of the new code (he), see http://wiki/Main/IIISynonyms.
 */
goog.LOCALE = 'en';  // default to en


/**
 * @define {boolean} Whether this code is running on trusted sites.
 *
 * On untrusted sites, several native functions can be defined or overridden by
 * external libraries like Prototype, Datejs, and JQuery and setting this flag
 * to false forces closure to use its own implementations when possible.
 *
 * If your javascript can be loaded by a third party site and you are wary about
 * relying on non-standard implementations, specify
 * "--define goog.TRUSTED_SITE=false" to the JSCompiler.
 */
goog.TRUSTED_SITE = true;


/**
 * Creates object stubs for a namespace.  The presence of one or more
 * goog.provide() calls indicate that the file defines the given
 * objects/namespaces.  Build tools also scan for provide/require statements
 * to discern dependencies, build dependency files (see deps.js), etc.
 * @see goog.require
 * @param {string} name Namespace provided by this file in the form
 *     "goog.package.part".
 */
goog.provide = function(name) {
  if (!COMPILED) {
    // Ensure that the same namespace isn't provided twice. This is intended
    // to teach new developers that 'goog.provide' is effectively a variable
    // declaration. And when JSCompiler transforms goog.provide into a real
    // variable declaration, the compiled JS should work the same as the raw
    // JS--even when the raw JS uses goog.provide incorrectly.
    if (goog.isProvided_(name)) {
      throw Error('Namespace "' + name + '" already declared.');
    }
    delete goog.implicitNamespaces_[name];

    var namespace = name;
    while ((namespace = namespace.substring(0, namespace.lastIndexOf('.')))) {
      if (goog.getObjectByName(namespace)) {
        break;
      }
      goog.implicitNamespaces_[namespace] = true;
    }
  }

  goog.exportPath_(name);
};


/**
 * Marks that the current file should only be used for testing, and never for
 * live code in production.
 * @param {string=} opt_message Optional message to add to the error that's
 *     raised when used in production code.
 */
goog.setTestOnly = function(opt_message) {
  if (COMPILED && !goog.DEBUG) {
    opt_message = opt_message || '';
    throw Error('Importing test-only code into non-debug environment' +
                opt_message ? ': ' + opt_message : '.');
  }
};


if (!COMPILED) {

  /**
   * Check if the given name has been goog.provided. This will return false for
   * names that are available only as implicit namespaces.
   * @param {string} name name of the object to look for.
   * @return {boolean} Whether the name has been provided.
   * @private
   */
  goog.isProvided_ = function(name) {
    return !goog.implicitNamespaces_[name] && !!goog.getObjectByName(name);
  };

  /**
   * Namespaces implicitly defined by goog.provide. For example,
   * goog.provide('goog.events.Event') implicitly declares
   * that 'goog' and 'goog.events' must be namespaces.
   *
   * @type {Object}
   * @private
   */
  goog.implicitNamespaces_ = {};
}


/**
 * Builds an object structure for the provided namespace path,
 * ensuring that names that already exist are not overwritten. For
 * example:
 * "a.b.c" -> a = {};a.b={};a.b.c={};
 * Used by goog.provide and goog.exportSymbol.
 * @param {string} name name of the object that this file defines.
 * @param {*=} opt_object the object to expose at the end of the path.
 * @param {Object=} opt_objectToExportTo The object to add the path to; default
 *     is |goog.global|.
 * @private
 */
goog.exportPath_ = function(name, opt_object, opt_objectToExportTo) {
  var parts = name.split('.');
  var cur = opt_objectToExportTo || goog.global;

  // Internet Explorer exhibits strange behavior when throwing errors from
  // methods externed in this manner.  See the testExportSymbolExceptions in
  // base_test.html for an example.
  if (!(parts[0] in cur) && cur.execScript) {
    cur.execScript('var ' + parts[0]);
  }

  // Certain browsers cannot parse code in the form for((a in b); c;);
  // This pattern is produced by the JSCompiler when it collapses the
  // statement above into the conditional loop below. To prevent this from
  // happening, use a for-loop and reserve the init logic as below.

  // Parentheses added to eliminate strict JS warning in Firefox.
  for (var part; parts.length && (part = parts.shift());) {
    if (!parts.length && goog.isDef(opt_object)) {
      // last part and we have an object; use it
      cur[part] = opt_object;
    } else if (cur[part]) {
      cur = cur[part];
    } else {
      cur = cur[part] = {};
    }
  }
};


/**
 * Returns an object based on its fully qualified external name.  If you are
 * using a compilation pass that renames property names beware that using this
 * function will not find renamed properties.
 *
 * @param {string} name The fully qualified name.
 * @param {Object=} opt_obj The object within which to look; default is
 *     |goog.global|.
 * @return {?} The value (object or primitive) or, if not found, null.
 */
goog.getObjectByName = function(name, opt_obj) {
  var parts = name.split('.');
  var cur = opt_obj || goog.global;
  for (var part; part = parts.shift(); ) {
    if (goog.isDefAndNotNull(cur[part])) {
      cur = cur[part];
    } else {
      return null;
    }
  }
  return cur;
};


/**
 * Globalizes a whole namespace, such as goog or goog.lang.
 *
 * @param {Object} obj The namespace to globalize.
 * @param {Object=} opt_global The object to add the properties to.
 * @deprecated Properties may be explicitly exported to the global scope, but
 *     this should no longer be done in bulk.
 */
goog.globalize = function(obj, opt_global) {
  var global = opt_global || goog.global;
  for (var x in obj) {
    global[x] = obj[x];
  }
};


/**
 * Adds a dependency from a file to the files it requires.
 * @param {string} relPath The path to the js file.
 * @param {Array} provides An array of strings with the names of the objects
 *                         this file provides.
 * @param {Array} requires An array of strings with the names of the objects
 *                         this file requires.
 */
goog.addDependency = function(relPath, provides, requires) {
  if (!COMPILED) {
    var provide, require;
    var path = relPath.replace(/\\/g, '/');
    var deps = goog.dependencies_;
    for (var i = 0; provide = provides[i]; i++) {
      deps.nameToPath[provide] = path;
      if (!(path in deps.pathToNames)) {
        deps.pathToNames[path] = {};
      }
      deps.pathToNames[path][provide] = true;
    }
    for (var j = 0; require = requires[j]; j++) {
      if (!(path in deps.requires)) {
        deps.requires[path] = {};
      }
      deps.requires[path][require] = true;
    }
  }
};




// NOTE(nnaze): The debug DOM loader was included in base.js as an orignal
// way to do "debug-mode" development.  The dependency system can sometimes
// be confusing, as can the debug DOM loader's asyncronous nature.
//
// With the DOM loader, a call to goog.require() is not blocking -- the
// script will not load until some point after the current script.  If a
// namespace is needed at runtime, it needs to be defined in a previous
// script, or loaded via require() with its registered dependencies.
// User-defined namespaces may need their own deps file.  See http://go/js_deps,
// http://go/genjsdeps, or, externally, DepsWriter.
// http://code.google.com/closure/library/docs/depswriter.html
//
// Because of legacy clients, the DOM loader can't be easily removed from
// base.js.  Work is being done to make it disableable or replaceable for
// different environments (DOM-less JavaScript interpreters like Rhino or V8,
// for example). See bootstrap/ for more information.


/**
 * @define {boolean} Whether to enable the debug loader.
 *
 * If enabled, a call to goog.require() will attempt to load the namespace by
 * appending a script tag to the DOM (if the namespace has been registered).
 *
 * If disabled, goog.require() will simply assert that the namespace has been
 * provided (and depend on the fact that some outside tool correctly ordered
 * the script).
 */
goog.ENABLE_DEBUG_LOADER = true;


/**
 * Implements a system for the dynamic resolution of dependencies
 * that works in parallel with the BUILD system. Note that all calls
 * to goog.require will be stripped by the JSCompiler when the
 * --closure_pass option is used.
 * @see goog.provide
 * @param {string} name Namespace to include (as was given in goog.provide())
 *     in the form "goog.package.part".
 */
goog.require = function(name) {

  // if the object already exists we do not need do do anything
  // TODO(arv): If we start to support require based on file name this has
  //            to change
  // TODO(arv): If we allow goog.foo.* this has to change
  // TODO(arv): If we implement dynamic load after page load we should probably
  //            not remove this code for the compiled output
  if (!COMPILED) {
    if (goog.isProvided_(name)) {
      return;
    }

    if (goog.ENABLE_DEBUG_LOADER) {
      var path = goog.getPathFromDeps_(name);
      if (path) {
        goog.included_[path] = true;
        goog.writeScripts_();
        return;
      }
    }

    var errorMessage = 'goog.require could not find: ' + name;
    if (goog.global.console) {
      goog.global.console['error'](errorMessage);
    }


      throw Error(errorMessage);

  }
};


/**
 * Path for included scripts
 * @type {string}
 */
goog.basePath = '';


/**
 * A hook for overriding the base path.
 * @type {string|undefined}
 */
goog.global.CLOSURE_BASE_PATH;


/**
 * Whether to write out Closure's deps file. By default,
 * the deps are written.
 * @type {boolean|undefined}
 */
goog.global.CLOSURE_NO_DEPS = true;


/**
 * A function to import a single script. This is meant to be overridden when
 * Closure is being run in non-HTML contexts, such as web workers. It's defined
 * in the global scope so that it can be set before base.js is loaded, which
 * allows deps.js to be imported properly.
 *
 * The function is passed the script source, which is a relative URI. It should
 * return true if the script was imported, false otherwise.
 */
goog.global.CLOSURE_IMPORT_SCRIPT;


/**
 * Null function used for default values of callbacks, etc.
 * @return {void} Nothing.
 */
goog.nullFunction = function() {};


/**
 * The identity function. Returns its first argument.
 *
 * @param {*=} opt_returnValue The single value that will be returned.
 * @param {...*} var_args Optional trailing arguments. These are ignored.
 * @return {?} The first argument. We can't know the type -- just pass it along
 *      without type.
 * @deprecated Use goog.functions.identity instead.
 */
goog.identityFunction = function(opt_returnValue, var_args) {
  return opt_returnValue;
};


/**
 * When defining a class Foo with an abstract method bar(), you can do:
 *
 * Foo.prototype.bar = goog.abstractMethod
 *
 * Now if a subclass of Foo fails to override bar(), an error
 * will be thrown when bar() is invoked.
 *
 * Note: This does not take the name of the function to override as
 * an argument because that would make it more difficult to obfuscate
 * our JavaScript code.
 *
 * @type {!Function}
 * @throws {Error} when invoked to indicate the method should be
 *   overridden.
 */
goog.abstractMethod = function() {
  throw Error('unimplemented abstract method');
};


/**
 * Adds a {@code getInstance} static method that always return the same instance
 * object.
 * @param {!Function} ctor The constructor for the class to add the static
 *     method to.
 */
goog.addSingletonGetter = function(ctor) {
  ctor.getInstance = function() {
    if (ctor.instance_) {
      return ctor.instance_;
    }
    if (goog.DEBUG) {
      // NOTE: JSCompiler can't optimize away Array#push.
      goog.instantiatedSingletons_[goog.instantiatedSingletons_.length] = ctor;
    }
    return ctor.instance_ = new ctor;
  };
};


/**
 * All singleton classes that have been instantiated, for testing. Don't read
 * it directly, use the {@code goog.testing.singleton} module. The compiler
 * removes this variable if unused.
 * @type {!Array.<!Function>}
 * @private
 */
goog.instantiatedSingletons_ = [];


if (!COMPILED && goog.ENABLE_DEBUG_LOADER) {
  /**
   * Object used to keep track of urls that have already been added. This
   * record allows the prevention of circular dependencies.
   * @type {Object}
   * @private
   */
  goog.included_ = {};


  /**
   * This object is used to keep track of dependencies and other data that is
   * used for loading scripts
   * @private
   * @type {Object}
   */
  goog.dependencies_ = {
    pathToNames: {}, // 1 to many
    nameToPath: {}, // 1 to 1
    requires: {}, // 1 to many
    // used when resolving dependencies to prevent us from
    // visiting the file twice
    visited: {},
    written: {} // used to keep track of script files we have written
  };


  /**
   * Tries to detect whether is in the context of an HTML document.
   * @return {boolean} True if it looks like HTML document.
   * @private
   */
  goog.inHtmlDocument_ = function() {
    var doc = goog.global.document;
    return typeof doc != 'undefined' &&
           'write' in doc;  // XULDocument misses write.
  };


  /**
   * Tries to detect the base path of the base.js script that bootstraps Closure
   * @private
   */
  goog.findBasePath_ = function() {
    if (goog.global.CLOSURE_BASE_PATH) {
      goog.basePath = goog.global.CLOSURE_BASE_PATH;
      return;
    } else if (!goog.inHtmlDocument_()) {
      return;
    }
    var doc = goog.global.document;
    var scripts = doc.getElementsByTagName('script');
    // Search backwards since the current script is in almost all cases the one
    // that has base.js.
    for (var i = scripts.length - 1; i >= 0; --i) {
      var src = scripts[i].src;
      var qmark = src.lastIndexOf('?');
      var l = qmark == -1 ? src.length : qmark;
      if (src.substr(l - 7, 7) == 'base.js') {
        goog.basePath = src.substr(0, l - 7);
        return;
      }
    }
  };


  /**
   * Imports a script if, and only if, that script hasn't already been imported.
   * (Must be called at execution time)
   * @param {string} src Script source.
   * @private
   */
  goog.importScript_ = function(src) {
    var importScript = goog.global.CLOSURE_IMPORT_SCRIPT ||
        goog.writeScriptTag_;
    if (!goog.dependencies_.written[src] && importScript(src)) {
      goog.dependencies_.written[src] = true;
    }
  };


  /**
   * The default implementation of the import function. Writes a script tag to
   * import the script.
   *
   * @param {string} src The script source.
   * @return {boolean} True if the script was imported, false otherwise.
   * @private
   */
  goog.writeScriptTag_ = function(src) {
    if (goog.inHtmlDocument_()) {
      var doc = goog.global.document;

      // If the user tries to require a new symbol after document load,
      // something has gone terribly wrong. Doing a document.write would
      // wipe out the page.
      if (doc.readyState == 'complete') {
        // Certain test frameworks load base.js multiple times, which tries
        // to write deps.js each time. If that happens, just fail silently.
        // These frameworks wipe the page between each load of base.js, so this
        // is OK.
        var isDeps = /\bdeps.js$/.test(src);
        if (isDeps) {
          return false;
        } else {
          throw Error('Cannot write "' + src + '" after document load');
        }
      }

      doc.write(
          '<script type="text/javascript" src="' + src + '"></' + 'script>');
      return true;
    } else {
      return false;
    }
  };


  /**
   * Resolves dependencies based on the dependencies added using addDependency
   * and calls importScript_ in the correct order.
   * @private
   */
  goog.writeScripts_ = function() {
    // the scripts we need to write this time
    var scripts = [];
    var seenScript = {};
    var deps = goog.dependencies_;

    function visitNode(path) {
      if (path in deps.written) {
        return;
      }

      // we have already visited this one. We can get here if we have cyclic
      // dependencies
      if (path in deps.visited) {
        if (!(path in seenScript)) {
          seenScript[path] = true;
          scripts.push(path);
        }
        return;
      }

      deps.visited[path] = true;

      if (path in deps.requires) {
        for (var requireName in deps.requires[path]) {
          // If the required name is defined, we assume that it was already
          // bootstrapped by other means.
          if (!goog.isProvided_(requireName)) {
            if (requireName in deps.nameToPath) {
              visitNode(deps.nameToPath[requireName]);
            } else {
              throw Error('Undefined nameToPath for ' + requireName);
            }
          }
        }
      }

      if (!(path in seenScript)) {
        seenScript[path] = true;
        scripts.push(path);
      }
    }

    for (var path in goog.included_) {
      if (!deps.written[path]) {
        visitNode(path);
      }
    }

    for (var i = 0; i < scripts.length; i++) {
      if (scripts[i]) {
        goog.importScript_(goog.basePath + scripts[i]);
      } else {
        throw Error('Undefined script input');
      }
    }
  };


  /**
   * Looks at the dependency rules and tries to determine the script file that
   * fulfills a particular rule.
   * @param {string} rule In the form goog.namespace.Class or project.script.
   * @return {?string} Url corresponding to the rule, or null.
   * @private
   */
  goog.getPathFromDeps_ = function(rule) {
    if (rule in goog.dependencies_.nameToPath) {
      return goog.dependencies_.nameToPath[rule];
    } else {
      return null;
    }
  };

  goog.findBasePath_();

  // Allow projects to manage the deps files themselves.
  if (!goog.global.CLOSURE_NO_DEPS) {
    goog.importScript_(goog.basePath + 'deps.js');
  }
}



//==============================================================================
// Language Enhancements
//==============================================================================


/**
 * This is a "fixed" version of the typeof operator.  It differs from the typeof
 * operator in such a way that null returns 'null' and arrays return 'array'.
 * @param {*} value The value to get the type of.
 * @return {string} The name of the type.
 */
goog.typeOf = function(value) {
  var s = typeof value;
  if (s == 'object') {
    if (value) {
      // Check these first, so we can avoid calling Object.prototype.toString if
      // possible.
      //
      // IE improperly marshals tyepof across execution contexts, but a
      // cross-context object will still return false for "instanceof Object".
      if (value instanceof Array) {
        return 'array';
      } else if (value instanceof Object) {
        return s;
      }

      // HACK: In order to use an Object prototype method on the arbitrary
      //   value, the compiler requires the value be cast to type Object,
      //   even though the ECMA spec explicitly allows it.
      var className = Object.prototype.toString.call(
          /** @type {Object} */ (value));
      // In Firefox 3.6, attempting to access iframe window objects' length
      // property throws an NS_ERROR_FAILURE, so we need to special-case it
      // here.
      if (className == '[object Window]') {
        return 'object';
      }

      // We cannot always use constructor == Array or instanceof Array because
      // different frames have different Array objects. In IE6, if the iframe
      // where the array was created is destroyed, the array loses its
      // prototype. Then dereferencing val.splice here throws an exception, so
      // we can't use goog.isFunction. Calling typeof directly returns 'unknown'
      // so that will work. In this case, this function will return false and
      // most array functions will still work because the array is still
      // array-like (supports length and []) even though it has lost its
      // prototype.
      // Mark Miller noticed that Object.prototype.toString
      // allows access to the unforgeable [[Class]] property.
      //  15.2.4.2 Object.prototype.toString ( )
      //  When the toString method is called, the following steps are taken:
      //      1. Get the [[Class]] property of this object.
      //      2. Compute a string value by concatenating the three strings
      //         "[object ", Result(1), and "]".
      //      3. Return Result(2).
      // and this behavior survives the destruction of the execution context.
      if ((className == '[object Array]' ||
           // In IE all non value types are wrapped as objects across window
           // boundaries (not iframe though) so we have to do object detection
           // for this edge case
           typeof value.length == 'number' &&
           typeof value.splice != 'undefined' &&
           typeof value.propertyIsEnumerable != 'undefined' &&
           !value.propertyIsEnumerable('splice')

          )) {
        return 'array';
      }
      // HACK: There is still an array case that fails.
      //     function ArrayImpostor() {}
      //     ArrayImpostor.prototype = [];
      //     var impostor = new ArrayImpostor;
      // this can be fixed by getting rid of the fast path
      // (value instanceof Array) and solely relying on
      // (value && Object.prototype.toString.vall(value) === '[object Array]')
      // but that would require many more function calls and is not warranted
      // unless closure code is receiving objects from untrusted sources.

      // IE in cross-window calls does not correctly marshal the function type
      // (it appears just as an object) so we cannot use just typeof val ==
      // 'function'. However, if the object has a call property, it is a
      // function.
      if ((className == '[object Function]' ||
          typeof value.call != 'undefined' &&
          typeof value.propertyIsEnumerable != 'undefined' &&
          !value.propertyIsEnumerable('call'))) {
        return 'function';
      }


    } else {
      return 'null';
    }

  } else if (s == 'function' && typeof value.call == 'undefined') {
    // In Safari typeof nodeList returns 'function', and on Firefox
    // typeof behaves similarly for HTML{Applet,Embed,Object}Elements
    // and RegExps.  We would like to return object for those and we can
    // detect an invalid function by making sure that the function
    // object has a call method.
    return 'object';
  }
  return s;
};


/**
 * Returns true if the specified value is not |undefined|.
 * WARNING: Do not use this to test if an object has a property. Use the in
 * operator instead.  Additionally, this function assumes that the global
 * undefined variable has not been redefined.
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is defined.
 */
goog.isDef = function(val) {
  return val !== undefined;
};


/**
 * Returns true if the specified value is |null|
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is null.
 */
goog.isNull = function(val) {
  return val === null;
};


/**
 * Returns true if the specified value is defined and not null
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is defined and not null.
 */
goog.isDefAndNotNull = function(val) {
  // Note that undefined == null.
  return val != null;
};


/**
 * Returns true if the specified value is an array
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is an array.
 */
goog.isArray = function(val) {
  return goog.typeOf(val) == 'array';
};


/**
 * Returns true if the object looks like an array. To qualify as array like
 * the value needs to be either a NodeList or an object with a Number length
 * property.
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is an array.
 */
goog.isArrayLike = function(val) {
  var type = goog.typeOf(val);
  return type == 'array' || type == 'object' && typeof val.length == 'number';
};


/**
 * Returns true if the object looks like a Date. To qualify as Date-like
 * the value needs to be an object and have a getFullYear() function.
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is a like a Date.
 */
goog.isDateLike = function(val) {
  return goog.isObject(val) && typeof val.getFullYear == 'function';
};


/**
 * Returns true if the specified value is a string
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is a string.
 */
goog.isString = function(val) {
  return typeof val == 'string';
};


/**
 * Returns true if the specified value is a boolean
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is boolean.
 */
goog.isBoolean = function(val) {
  return typeof val == 'boolean';
};


/**
 * Returns true if the specified value is a number
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is a number.
 */
goog.isNumber = function(val) {
  return typeof val == 'number';
};


/**
 * Returns true if the specified value is a function
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is a function.
 */
goog.isFunction = function(val) {
  return goog.typeOf(val) == 'function';
};


/**
 * Returns true if the specified value is an object.  This includes arrays
 * and functions.
 * @param {*} val Variable to test.
 * @return {boolean} Whether variable is an object.
 */
goog.isObject = function(val) {
  var type = typeof val;
  return type == 'object' && val != null || type == 'function';
  // return Object(val) === val also works, but is slower, especially if val is
  // not an object.
};


/**
 * Gets a unique ID for an object. This mutates the object so that further
 * calls with the same object as a parameter returns the same value. The unique
 * ID is guaranteed to be unique across the current session amongst objects that
 * are passed into {@code getUid}. There is no guarantee that the ID is unique
 * or consistent across sessions. It is unsafe to generate unique ID for
 * function prototypes.
 *
 * @param {Object} obj The object to get the unique ID for.
 * @return {number} The unique ID for the object.
 */
goog.getUid = function(obj) {
  // TODO(arv): Make the type stricter, do not accept null.

  // In Opera window.hasOwnProperty exists but always returns false so we avoid
  // using it. As a consequence the unique ID generated for BaseClass.prototype
  // and SubClass.prototype will be the same.
  return obj[goog.UID_PROPERTY_] ||
      (obj[goog.UID_PROPERTY_] = ++goog.uidCounter_);
};


/**
 * Removes the unique ID from an object. This is useful if the object was
 * previously mutated using {@code goog.getUid} in which case the mutation is
 * undone.
 * @param {Object} obj The object to remove the unique ID field from.
 */
goog.removeUid = function(obj) {
  // TODO(arv): Make the type stricter, do not accept null.

  // DOM nodes in IE are not instance of Object and throws exception
  // for delete. Instead we try to use removeAttribute
  if ('removeAttribute' in obj) {
    obj.removeAttribute(goog.UID_PROPERTY_);
  }
  /** @preserveTry */
  try {
    delete obj[goog.UID_PROPERTY_];
  } catch (ex) {
  }
};


/**
 * Name for unique ID property. Initialized in a way to help avoid collisions
 * with other closure javascript on the same page.
 * @type {string}
 * @private
 */
goog.UID_PROPERTY_ = 'closure_uid_' + ((Math.random() * 1e9) >>> 0);


/**
 * Counter for UID.
 * @type {number}
 * @private
 */
goog.uidCounter_ = 0;


/**
 * Adds a hash code field to an object. The hash code is unique for the
 * given object.
 * @param {Object} obj The object to get the hash code for.
 * @return {number} The hash code for the object.
 * @deprecated Use goog.getUid instead.
 */
goog.getHashCode = goog.getUid;


/**
 * Removes the hash code field from an object.
 * @param {Object} obj The object to remove the field from.
 * @deprecated Use goog.removeUid instead.
 */
goog.removeHashCode = goog.removeUid;


/**
 * Clones a value. The input may be an Object, Array, or basic type. Objects and
 * arrays will be cloned recursively.
 *
 * WARNINGS:
 * <code>goog.cloneObject</code> does not detect reference loops. Objects that
 * refer to themselves will cause infinite recursion.
 *
 * <code>goog.cloneObject</code> is unaware of unique identifiers, and copies
 * UIDs created by <code>getUid</code> into cloned results.
 *
 * @param {*} obj The value to clone.
 * @return {*} A clone of the input value.
 * @deprecated goog.cloneObject is unsafe. Prefer the goog.object methods.
 */
goog.cloneObject = function(obj) {
  var type = goog.typeOf(obj);
  if (type == 'object' || type == 'array') {
    if (obj.clone) {
      return obj.clone();
    }
    var clone = type == 'array' ? [] : {};
    for (var key in obj) {
      clone[key] = goog.cloneObject(obj[key]);
    }
    return clone;
  }

  return obj;
};


/**
 * A native implementation of goog.bind.
 * @param {Function} fn A function to partially apply.
 * @param {Object|undefined} selfObj Specifies the object which |this| should
 *     point to when the function is run.
 * @param {...*} var_args Additional arguments that are partially
 *     applied to the function.
 * @return {!Function} A partially-applied form of the function bind() was
 *     invoked as a method of.
 * @private
 * @suppress {deprecated} The compiler thinks that Function.prototype.bind
 *     is deprecated because some people have declared a pure-JS version.
 *     Only the pure-JS version is truly deprecated.
 */
goog.bindNative_ = function(fn, selfObj, var_args) {
  return /** @type {!Function} */ (fn.call.apply(fn.bind, arguments));
};


/**
 * A pure-JS implementation of goog.bind.
 * @param {Function} fn A function to partially apply.
 * @param {Object|undefined} selfObj Specifies the object which |this| should
 *     point to when the function is run.
 * @param {...*} var_args Additional arguments that are partially
 *     applied to the function.
 * @return {!Function} A partially-applied form of the function bind() was
 *     invoked as a method of.
 * @private
 */
goog.bindJs_ = function(fn, selfObj, var_args) {
  if (!fn) {
    throw new Error();
  }

  if (arguments.length > 2) {
    var boundArgs = Array.prototype.slice.call(arguments, 2);
    return function() {
      // Prepend the bound arguments to the current arguments.
      var newArgs = Array.prototype.slice.call(arguments);
      Array.prototype.unshift.apply(newArgs, boundArgs);
      return fn.apply(selfObj, newArgs);
    };

  } else {
    return function() {
      return fn.apply(selfObj, arguments);
    };
  }
};


/**
 * Partially applies this function to a particular 'this object' and zero or
 * more arguments. The result is a new function with some arguments of the first
 * function pre-filled and the value of |this| 'pre-specified'.<br><br>
 *
 * Remaining arguments specified at call-time are appended to the pre-
 * specified ones.<br><br>
 *
 * Also see: {@link #partial}.<br><br>
 *
 * Usage:
 * <pre>var barMethBound = bind(myFunction, myObj, 'arg1', 'arg2');
 * barMethBound('arg3', 'arg4');</pre>
 *
 * @param {Function} fn A function to partially apply.
 * @param {Object|undefined} selfObj Specifies the object which |this| should
 *     point to when the function is run.
 * @param {...*} var_args Additional arguments that are partially
 *     applied to the function.
 * @return {!Function} A partially-applied form of the function bind() was
 *     invoked as a method of.
 * @suppress {deprecated} See above.
 */
goog.bind = function(fn, selfObj, var_args) {
  // TODO(nicksantos): narrow the type signature.
  if (Function.prototype.bind &&
      // NOTE(nicksantos): Somebody pulled base.js into the default
      // Chrome extension environment. This means that for Chrome extensions,
      // they get the implementation of Function.prototype.bind that
      // calls goog.bind instead of the native one. Even worse, we don't want
      // to introduce a circular dependency between goog.bind and
      // Function.prototype.bind, so we have to hack this to make sure it
      // works correctly.
      Function.prototype.bind.toString().indexOf('native code') != -1) {
    goog.bind = goog.bindNative_;
  } else {
    goog.bind = goog.bindJs_;
  }
  return goog.bind.apply(null, arguments);
};


/**
 * Like bind(), except that a 'this object' is not required. Useful when the
 * target function is already bound.
 *
 * Usage:
 * var g = partial(f, arg1, arg2);
 * g(arg3, arg4);
 *
 * @param {Function} fn A function to partially apply.
 * @param {...*} var_args Additional arguments that are partially
 *     applied to fn.
 * @return {!Function} A partially-applied form of the function bind() was
 *     invoked as a method of.
 */
goog.partial = function(fn, var_args) {
  var args = Array.prototype.slice.call(arguments, 1);
  return function() {
    // Prepend the bound arguments to the current arguments.
    var newArgs = Array.prototype.slice.call(arguments);
    newArgs.unshift.apply(newArgs, args);
    return fn.apply(this, newArgs);
  };
};


/**
 * Copies all the members of a source object to a target object. This method
 * does not work on all browsers for all objects that contain keys such as
 * toString or hasOwnProperty. Use goog.object.extend for this purpose.
 * @param {Object} target Target.
 * @param {Object} source Source.
 */
goog.mixin = function(target, source) {
  for (var x in source) {
    target[x] = source[x];
  }

  // For IE7 or lower, the for-in-loop does not contain any properties that are
  // not enumerable on the prototype object (for example, isPrototypeOf from
  // Object.prototype) but also it will not include 'replace' on objects that
  // extend String and change 'replace' (not that it is common for anyone to
  // extend anything except Object).
};


/**
 * @return {number} An integer value representing the number of milliseconds
 *     between midnight, January 1, 1970 and the current time.
 */
goog.now = (goog.TRUSTED_SITE && Date.now) || (function() {
  // Unary plus operator converts its operand to a number which in the case of
  // a date is done by calling getTime().
  return +new Date();
});


/**
 * Evals javascript in the global scope.  In IE this uses execScript, other
 * browsers use goog.global.eval. If goog.global.eval does not evaluate in the
 * global scope (for example, in Safari), appends a script tag instead.
 * Throws an exception if neither execScript or eval is defined.
 * @param {string} script JavaScript string.
 */
goog.globalEval = function(script) {
  if (goog.global.execScript) {
    goog.global.execScript(script, 'JavaScript');
  } else if (goog.global.eval) {
    // Test to see if eval works
    if (goog.evalWorksForGlobals_ == null) {
      goog.global.eval('var _et_ = 1;');
      if (typeof goog.global['_et_'] != 'undefined') {
        delete goog.global['_et_'];
        goog.evalWorksForGlobals_ = true;
      } else {
        goog.evalWorksForGlobals_ = false;
      }
    }

    if (goog.evalWorksForGlobals_) {
      goog.global.eval(script);
    } else {
      var doc = goog.global.document;
      var scriptElt = doc.createElement('script');
      scriptElt.type = 'text/javascript';
      scriptElt.defer = false;
      // Note(user): can't use .innerHTML since "t('<test>')" will fail and
      // .text doesn't work in Safari 2.  Therefore we append a text node.
      scriptElt.appendChild(doc.createTextNode(script));
      doc.body.appendChild(scriptElt);
      doc.body.removeChild(scriptElt);
    }
  } else {
    throw Error('goog.globalEval not available');
  }
};


/**
 * Indicates whether or not we can call 'eval' directly to eval code in the
 * global scope. Set to a Boolean by the first call to goog.globalEval (which
 * empirically tests whether eval works for globals). @see goog.globalEval
 * @type {?boolean}
 * @private
 */
goog.evalWorksForGlobals_ = null;


/**
 * Optional map of CSS class names to obfuscated names used with
 * goog.getCssName().
 * @type {Object|undefined}
 * @private
 * @see goog.setCssNameMapping
 */
goog.cssNameMapping_;


/**
 * Optional obfuscation style for CSS class names. Should be set to either
 * 'BY_WHOLE' or 'BY_PART' if defined.
 * @type {string|undefined}
 * @private
 * @see goog.setCssNameMapping
 */
goog.cssNameMappingStyle_;


/**
 * Handles strings that are intended to be used as CSS class names.
 *
 * This function works in tandem with @see goog.setCssNameMapping.
 *
 * Without any mapping set, the arguments are simple joined with a
 * hyphen and passed through unaltered.
 *
 * When there is a mapping, there are two possible styles in which
 * these mappings are used. In the BY_PART style, each part (i.e. in
 * between hyphens) of the passed in css name is rewritten according
 * to the map. In the BY_WHOLE style, the full css name is looked up in
 * the map directly. If a rewrite is not specified by the map, the
 * compiler will output a warning.
 *
 * When the mapping is passed to the compiler, it will replace calls
 * to goog.getCssName with the strings from the mapping, e.g.
 *     var x = goog.getCssName('foo');
 *     var y = goog.getCssName(this.baseClass, 'active');
 *  becomes:
 *     var x= 'foo';
 *     var y = this.baseClass + '-active';
 *
 * If one argument is passed it will be processed, if two are passed
 * only the modifier will be processed, as it is assumed the first
 * argument was generated as a result of calling goog.getCssName.
 *
 * @param {string} className The class name.
 * @param {string=} opt_modifier A modifier to be appended to the class name.
 * @return {string} The class name or the concatenation of the class name and
 *     the modifier.
 */
goog.getCssName = function(className, opt_modifier) {
  var getMapping = function(cssName) {
    return goog.cssNameMapping_[cssName] || cssName;
  };

  var renameByParts = function(cssName) {
    // Remap all the parts individually.
    var parts = cssName.split('-');
    var mapped = [];
    for (var i = 0; i < parts.length; i++) {
      mapped.push(getMapping(parts[i]));
    }
    return mapped.join('-');
  };

  var rename;
  if (goog.cssNameMapping_) {
    rename = goog.cssNameMappingStyle_ == 'BY_WHOLE' ?
        getMapping : renameByParts;
  } else {
    rename = function(a) {
      return a;
    };
  }

  if (opt_modifier) {
    return className + '-' + rename(opt_modifier);
  } else {
    return rename(className);
  }
};


/**
 * Sets the map to check when returning a value from goog.getCssName(). Example:
 * <pre>
 * goog.setCssNameMapping({
 *   "goog": "a",
 *   "disabled": "b",
 * });
 *
 * var x = goog.getCssName('goog');
 * // The following evaluates to: "a a-b".
 * goog.getCssName('goog') + ' ' + goog.getCssName(x, 'disabled')
 * </pre>
 * When declared as a map of string literals to string literals, the JSCompiler
 * will replace all calls to goog.getCssName() using the supplied map if the
 * --closure_pass flag is set.
 *
 * @param {!Object} mapping A map of strings to strings where keys are possible
 *     arguments to goog.getCssName() and values are the corresponding values
 *     that should be returned.
 * @param {string=} opt_style The style of css name mapping. There are two valid
 *     options: 'BY_PART', and 'BY_WHOLE'.
 * @see goog.getCssName for a description.
 */
goog.setCssNameMapping = function(mapping, opt_style) {
  goog.cssNameMapping_ = mapping;
  goog.cssNameMappingStyle_ = opt_style;
};


/**
 * To use CSS renaming in compiled mode, one of the input files should have a
 * call to goog.setCssNameMapping() with an object literal that the JSCompiler
 * can extract and use to replace all calls to goog.getCssName(). In uncompiled
 * mode, JavaScript code should be loaded before this base.js file that declares
 * a global variable, CLOSURE_CSS_NAME_MAPPING, which is used below. This is
 * to ensure that the mapping is loaded before any calls to goog.getCssName()
 * are made in uncompiled mode.
 *
 * A hook for overriding the CSS name mapping.
 * @type {Object|undefined}
 */
goog.global.CLOSURE_CSS_NAME_MAPPING;


if (!COMPILED && goog.global.CLOSURE_CSS_NAME_MAPPING) {
  // This does not call goog.setCssNameMapping() because the JSCompiler
  // requires that goog.setCssNameMapping() be called with an object literal.
  goog.cssNameMapping_ = goog.global.CLOSURE_CSS_NAME_MAPPING;
}


/**
 * Gets a localized message.
 *
 * This function is a compiler primitive. If you give the compiler a localized
 * message bundle, it will replace the string at compile-time with a localized
 * version, and expand goog.getMsg call to a concatenated string.
 *
 * Messages must be initialized in the form:
 * <code>
 * var MSG_NAME = goog.getMsg('Hello {$placeholder}', {'placeholder': 'world'});
 * </code>
 *
 * @param {string} str Translatable string, places holders in the form {$foo}.
 * @param {Object=} opt_values Map of place holder name to value.
 * @return {string} message with placeholders filled.
 */
goog.getMsg = function(str, opt_values) {
  var values = opt_values || {};
  for (var key in values) {
    var value = ('' + values[key]).replace(/\$/g, '$$$$');
    str = str.replace(new RegExp('\\{\\$' + key + '\\}', 'gi'), value);
  }
  return str;
};


/**
 * Gets a localized message. If the message does not have a translation, gives a
 * fallback message.
 *
 * This is useful when introducing a new message that has not yet been
 * translated into all languages.
 *
 * This function is a compiler primtive. Must be used in the form:
 * <code>var x = goog.getMsgWithFallback(MSG_A, MSG_B);</code>
 * where MSG_A and MSG_B were initialized with goog.getMsg.
 *
 * @param {string} a The preferred message.
 * @param {string} b The fallback message.
 * @return {string} The best translated message.
 */
goog.getMsgWithFallback = function(a, b) {
  return a;
};


/**
 * Exposes an unobfuscated global namespace path for the given object.
 * Note that fields of the exported object *will* be obfuscated,
 * unless they are exported in turn via this function or
 * goog.exportProperty
 *
 * <p>Also handy for making public items that are defined in anonymous
 * closures.
 *
 * ex. goog.exportSymbol('public.path.Foo', Foo);
 *
 * ex. goog.exportSymbol('public.path.Foo.staticFunction',
 *                       Foo.staticFunction);
 *     public.path.Foo.staticFunction();
 *
 * ex. goog.exportSymbol('public.path.Foo.prototype.myMethod',
 *                       Foo.prototype.myMethod);
 *     new public.path.Foo().myMethod();
 *
 * @param {string} publicPath Unobfuscated name to export.
 * @param {*} object Object the name should point to.
 * @param {Object=} opt_objectToExportTo The object to add the path to; default
 *     is |goog.global|.
 */
goog.exportSymbol = function(publicPath, object, opt_objectToExportTo) {
  goog.exportPath_(publicPath, object, opt_objectToExportTo);
};


/**
 * Exports a property unobfuscated into the object's namespace.
 * ex. goog.exportProperty(Foo, 'staticFunction', Foo.staticFunction);
 * ex. goog.exportProperty(Foo.prototype, 'myMethod', Foo.prototype.myMethod);
 * @param {Object} object Object whose static property is being exported.
 * @param {string} publicName Unobfuscated name to export.
 * @param {*} symbol Object the name should point to.
 */
goog.exportProperty = function(object, publicName, symbol) {
  object[publicName] = symbol;
};


/**
 * Inherit the prototype methods from one constructor into another.
 *
 * Usage:
 * <pre>
 * function ParentClass(a, b) { }
 * ParentClass.prototype.foo = function(a) { }
 *
 * function ChildClass(a, b, c) {
 *   goog.base(this, a, b);
 * }
 * goog.inherits(ChildClass, ParentClass);
 *
 * var child = new ChildClass('a', 'b', 'see');
 * child.foo(); // works
 * </pre>
 *
 * In addition, a superclass' implementation of a method can be invoked
 * as follows:
 *
 * <pre>
 * ChildClass.prototype.foo = function(a) {
 *   ChildClass.superClass_.foo.call(this, a);
 *   // other code
 * };
 * </pre>
 *
 * @param {Function} childCtor Child class.
 * @param {Function} parentCtor Parent class.
 */
goog.inherits = function(childCtor, parentCtor) {
  /** @constructor */
  function tempCtor() {};
  tempCtor.prototype = parentCtor.prototype;
  childCtor.superClass_ = parentCtor.prototype;
  childCtor.prototype = new tempCtor();
  /** @override */
  childCtor.prototype.constructor = childCtor;
};


/**
 * Call up to the superclass.
 *
 * If this is called from a constructor, then this calls the superclass
 * contructor with arguments 1-N.
 *
 * If this is called from a prototype method, then you must pass
 * the name of the method as the second argument to this function. If
 * you do not, you will get a runtime error. This calls the superclass'
 * method with arguments 2-N.
 *
 * This function only works if you use goog.inherits to express
 * inheritance relationships between your classes.
 *
 * This function is a compiler primitive. At compile-time, the
 * compiler will do macro expansion to remove a lot of
 * the extra overhead that this function introduces. The compiler
 * will also enforce a lot of the assumptions that this function
 * makes, and treat it as a compiler error if you break them.
 *
 * @param {!Object} me Should always be "this".
 * @param {*=} opt_methodName The method name if calling a super method.
 * @param {...*} var_args The rest of the arguments.
 * @return {*} The return value of the superclass method.
 */
goog.base = function(me, opt_methodName, var_args) {
  var caller = arguments.callee.caller;
  if (caller.superClass_) {
    // This is a constructor. Call the superclass constructor.
    return caller.superClass_.constructor.apply(
        me, Array.prototype.slice.call(arguments, 1));
  }

  var args = Array.prototype.slice.call(arguments, 2);
  var foundCaller = false;
  for (var ctor = me.constructor;
       ctor; ctor = ctor.superClass_ && ctor.superClass_.constructor) {
    if (ctor.prototype[opt_methodName] === caller) {
      foundCaller = true;
    } else if (foundCaller) {
      return ctor.prototype[opt_methodName].apply(me, args);
    }
  }

  // If we did not find the caller in the prototype chain,
  // then one of two things happened:
  // 1) The caller is an instance method.
  // 2) This method was not called by the right caller.
  if (me[opt_methodName] === caller) {
    return me.constructor.prototype[opt_methodName].apply(me, args);
  } else {
    throw Error(
        'goog.base called from a method of one name ' +
        'to a method of a different name');
  }
};


/**
 * Allow for aliasing within scope functions.  This function exists for
 * uncompiled code - in compiled code the calls will be inlined and the
 * aliases applied.  In uncompiled code the function is simply run since the
 * aliases as written are valid JavaScript.
 * @param {function()} fn Function to call.  This function can contain aliases
 *     to namespaces (e.g. "var dom = goog.dom") or classes
 *    (e.g. "var Timer = goog.Timer").
 */
goog.scope = function(fn) {
  fn.call(goog.global);
};



goog.provide('webfont.DomHelper');

/**
 * Handles common DOM manipulation tasks. The aim of this library is to cover
 * the needs of typical font loading. Not more, not less.
 * @param {Window} mainWindow The main window webfontloader.js is loaded in.
 * @param {Window=} opt_loadWindow The window we'll load the font into. By
 *   default, the main window is used.
 * @constructor
 */
webfont.DomHelper = function(mainWindow, opt_loadWindow) {
  this.mainWindow_ = mainWindow;
  this.loadWindow_ = opt_loadWindow || mainWindow;

  /** @type {Document} */
  this.document_ = this.loadWindow_.document;

  /** @type {boolean|undefined} */
  this.supportForStyle_ = undefined;
};

goog.scope(function () {
  var DomHelper = webfont.DomHelper;

  /**
   * Creates an element.
   * @param {string} elem The element type.
   * @param {Object=} opt_attr A hash of attribute key/value pairs.
   * @param {string=} opt_innerHtml Contents of the element.
   * @return {Element} the new element.
   */
  DomHelper.prototype.createElement = function(elem, opt_attr,
      opt_innerHtml) {
    var domElement = this.document_.createElement(elem);

    if (opt_attr) {
      for (var attr in opt_attr) {
        // protect against native prototype augmentations
        if (opt_attr.hasOwnProperty(attr)) {
          if (attr == "style") {
            this.setStyle(domElement, opt_attr[attr]);
          } else {
            domElement.setAttribute(attr, opt_attr[attr]);
          }
        }
      }
    }
    if (opt_innerHtml) {
      domElement.appendChild(this.document_.createTextNode(opt_innerHtml));
    }
    return domElement;
  };

  /**
   * Inserts an element into the document. This is intended for unambiguous
   * elements such as html, body, head.
   * @param {string} tagName The element name.
   * @param {Element} e The element to append.
   * @return {boolean} True if the element was inserted.
   */
  DomHelper.prototype.insertInto = function(tagName, e) {
    var t = this.document_.getElementsByTagName(tagName)[0];

    if (!t) { // opera allows documents without a head
      t = document.documentElement;
    }

    if (t && t.lastChild) {
      // This is safer than appendChild in IE. appendChild causes random
      // JS errors in IE. Sometimes errors in other JS exectution, sometimes
      // complete 'This page cannot be displayed' errors. For our purposes,
      // it's equivalent because we don't need to insert at any specific
      // location.
      t.insertBefore(e, t.lastChild);
      return true;
    }
    return false;
  };

  /**
   * Calls a function when the body tag exists.
   * @param {function()} callback The function to call.
   */
  DomHelper.prototype.whenBodyExists = function(callback) {
    var that = this;
    var check = function() {
      if (that.document_.body) {
        callback();
      } else {
        setTimeout(check, 0);
      }
    }
    check();
  };

  /**
   * Removes an element from the DOM.
   * @param {Element} node The element to remove.
   * @return {boolean} True if the element was removed.
   */
  DomHelper.prototype.removeElement = function(node) {
    if (node.parentNode) {
      node.parentNode.removeChild(node);
      return true;
    }
    return false;
  };

  /**
   * Creates a link to a CSS document.
   * @param {string} src The URL of the stylesheet.
   * @return {Element} a link element.
   */
  DomHelper.prototype.createCssLink = function(src) {
    return this.createElement('link', {
      'rel': 'stylesheet',
      'href': src
    });
  };

  /**
   * Creates a link to a javascript document.
   * @param {string} src The URL of the script.
   * @return {Element} a script element.
   */
  DomHelper.prototype.createScriptSrc = function(src) {
    return this.createElement('script', {
      'src': src
    });
  };

  /**
   * Appends a name to an element's class attribute.
   * @param {Element} e The element.
   * @param {string} name The class name to add.
   */
  DomHelper.prototype.appendClassName = function(e, name) {
    var classes = e.className.split(/\s+/);
    for (var i = 0, len = classes.length; i < len; i++) {
      if (classes[i] == name) {
        return;
      }
    }
    classes.push(name);
    e.className = classes.join(' ')
                    .replace(/\s+/g, ' ')
                    .replace(/^\s+|\s+$/, '');
  };

  /**
   * Removes a name to an element's class attribute.
   * @param {Element} e The element.
   * @param {string} name The class name to remove.
   */
  DomHelper.prototype.removeClassName = function(e, name) {
    var classes = e.className.split(/\s+/);
    var remainingClasses = [];
    for (var i = 0, len = classes.length; i < len; i++) {
      if (classes[i] != name) {
        remainingClasses.push(classes[i]);
      }
    }
    e.className = remainingClasses.join(' ')
                    .replace(/\s+/g, ' ')
                    .replace(/^\s+|\s+$/, '');
  };

  /**
   * Returns true if an element has a given class name and false otherwise.
   * @param {Element} e The element.
   * @param {string} name The class name to check for.
   * @return {boolean} Whether or not the element has this class name.
   */
  DomHelper.prototype.hasClassName = function(e, name) {
    var classes = e.className.split(/\s+/);
    for (var i = 0, len = classes.length; i < len; i++) {
      if (classes[i] == name) {
        return true;
      }
    }
    return false;
  };

  /**
   * Sets the style attribute on an element.
   * @param {Element} e The element.
   * @param {string} styleString The style string.
   */
  DomHelper.prototype.setStyle = function(e, styleString) {
    if (this.hasSupportForStyle_()) {
      e.setAttribute("style", styleString);
    } else {
      e.style.cssText = styleString;
    }
  };

  /**
   * Check if getting and setting the style attribute on an element with
   * getAttribute/setAttribute is supported. In old IE, you must use style.cssText
   * instead. Feature detection is only done the first time this is called.
   * @private
   * @return {boolean} Whether or not the feature is supported.
   */
  DomHelper.prototype.hasSupportForStyle_ = function() {
    if (this.supportForStyle_ === undefined) {
      var e = this.document_.createElement('p');
      e.innerHTML = '<a style="top:1px;">w</a>';
      this.supportForStyle_ = /top/.test(e.getElementsByTagName('a')[0].getAttribute('style'));
    }
    return this.supportForStyle_
  };

  /**
   * @return {Window} The main window webfontloader.js is loaded in (for config).
   */
  DomHelper.prototype.getMainWindow = function() {
    return this.mainWindow_;
  };

  /**
   * @return {Window} The window that we're loading the font(s) into.
   */
  DomHelper.prototype.getLoadWindow = function() {
    return this.loadWindow_;
  };

  /**
   * @return {string} The protocol (http: or https:) to request resources in.
   */
  DomHelper.prototype.getProtocol = function() {
    var protocol = this.loadWindow_.location.protocol;
    // For empty iframes, fallback to main window's protocol.
    if (protocol == 'about:') {
      protocol = this.mainWindow_.location.protocol;
    }
    return protocol == 'https:' ? 'https:' : 'http:';
  };

  /**
   * Returns the secure status of the current document.
   * @return {boolean} true if the current document is served securely.
   */
  DomHelper.prototype.isHttps = function() {
    return this.getProtocol() === 'https:';
  };

  /**
   * Returns the hostname of the current document.
   * @return {string} hostname.
   */
  DomHelper.prototype.getHostName = function() {
    return this.getLoadWindow().location.hostname || this.getMainWindow().location.hostname;
  };

  /**
   * Creates a style element.
   * @param {string} css Contents of the style element.
   * @return {Element} a DOM element.
   */
  DomHelper.prototype.createStyle = function(css) {
    var e = this.document_.createElement('style');

    e.setAttribute('type', 'text/css');
    if (e.styleSheet) { // IE
      e.styleSheet.cssText = css;
    } else {
      e.appendChild(document.createTextNode(css));
    }
    return e;
  };

  /**
   * Loads an external script file.
   * @param {string} src URL of the script.
   * @param {function()=} opt_callback callback when the script has loaded.
   */
  DomHelper.prototype.loadScript = function(src, opt_callback) {
    var head = this.document_.getElementsByTagName('head')[0];

    if (head) {
      var script = this.document_.createElement('script');
      script.src = src;
      var done = false;
      script.onload = script.onreadystatechange = function() {
        if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
          done = true;
          if (opt_callback) {
            opt_callback();
          }
          script.onload = script.onreadystatechange = null;
          // Avoid a bizarre issue with unclosed <base> tag in IE6 - http://blog.dotsmart.net/2008/04/
          if (script.parentNode.tagName == 'HEAD') head.removeChild(script);
        }
      };
      head.appendChild(script);
    }
  };
});

goog.provide('webfont.BrowserInfo');

/**
 * @export
 * @constructor
 * @param {boolean} webfontSupport
 * @param {boolean} webKitFallbackBug
 * @param {boolean} webKitMetricsBug
 */
webfont.BrowserInfo = function (webfontSupport, webKitFallbackBug, webKitMetricsBug) {
  this.webfontSupport_ = webfontSupport;
  this.webKitFallbackBug_ = webKitFallbackBug;
  this.webKitMetricsBug_ = webKitMetricsBug;
};

goog.scope(function () {
  var BrowserInfo = webfont.BrowserInfo;

  /**
   * @export
   * Returns true if the browser supports web fonts.
   *
   * @return {boolean}
   */
  BrowserInfo.prototype.hasWebFontSupport = function () {
    return this.webfontSupport_;
  };

  /**
   * @export
   *
   * Returns true if the browser has the WebKit fallback bug.
   *
   * The bug causes the normal CSS font stack to be ignored while
   * loading web fonts. Instead it picks the generic font family
   * (or the default generic font family) of the first instance
   * the web font is mentioned in CSS. It switches to this font
   * immediately while loading web font, causing two changes in
   * font to occur (compared to other browsers which only change
   * font once the web font has loaded.)
   *
   * The bug has been fixed and is only happens in WebKit versions
   * below 536.11. Even though it is fixed we still have a large
   * percentage of users on older WebKit versions, mostly on mobile
   * platforms.
   *
   * Also see: https://bugs.webkit.org/show_bug.cgi?id=76684
   *
   * @return {boolean}
   */
  BrowserInfo.prototype.hasWebKitFallbackBug = function () {
    return this.webKitFallbackBug_;
  };

  /**
   * @export
   *
   * Returns true if the browser has the WebKit metrics bug
   *
   * The metrics bug causes WebKit to change the height of a font
   * while loading a web font. Other browsers do not modify
   * the width or height of the fallback font while a web font is
   * loading. This caused our width and height check to be incorrect,
   * triggering a false positive.
   *
   * Also see: https://bugs.webkit.org/show_bug.cgi?id=110977
   *
   * @return {boolean}
   */
  BrowserInfo.prototype.hasWebKitMetricsBug = function () {
    return this.webKitMetricsBug_;
  };
});

goog.provide('webfont.Version');

/**
 * Represents a version as used in user agent strings. Note
 * that this does not represent any sort of reliable versioning
 * scheme (like Semantic Versioning) but merely a best effort
 * at parsing a large amount of wildly different version strings.
 *
 * @constructor
 * @param {?number=} opt_major
 * @param {?number=} opt_minor
 * @param {?number=} opt_patch
 * @param {?(number|string)=} opt_build
 */
webfont.Version = function(opt_major, opt_minor, opt_patch, opt_build) {
  /**
   * @type {?number}
   */
  this.major = goog.isDefAndNotNull(opt_major) ? opt_major : null;

  /**
   * @type {?number}
   */
  this.minor = goog.isDefAndNotNull(opt_minor) ? opt_minor : null;

  /**
   * @type {?number}
   */
  this.patch = goog.isDefAndNotNull(opt_patch) ? opt_patch : null;

  /**
   * @type {?(number|string)}
   */
  this.build = goog.isDefAndNotNull(opt_build) ? opt_build : null;
}

goog.scope(function () {
  var Version = webfont.Version;

  Version.TOKENIZER = new RegExp(
    "^" +
    "([0-9]+)" +            // major
    "(?:" +
      "[\\._-]([0-9]+)" +   // minor
    ")?" +
    "(?:" +
      "[\\._-]([0-9]+)" +  // patch
    ")?" +
    "(?:" +
      "[\\._+-]?(.*)" +    // build
    ")?$"
  );

  /**
   * Returns true if the version is valid. A
   * version is considered valid if it has at
   * least a major version number.
   *
   * @return {boolean}
   */
  Version.prototype.isValid = function () {
    return !goog.isNull(this.major);
  };

  /**
   * Compares two versions. Returns -1 if this
   * is smaller than version. Returns 1 if this
   * is greater than version. Returns 0 if this
   * equals version.
   *
   * Build strings or numbers are ignored when
   * comparing versions.
   *
   * @param {webfont.Version} version
   * @return {number}
   */
  Version.prototype.compare = function (version) {
    if (this.major > version.major ||
        ((this.major === version.major && this.minor > version.minor) ||
          (this.major === version.major && this.minor === version.minor && this.patch > version.patch))) {
      return 1;
    } else if (this.major < version.major ||
               ((this.major === version.major && this.minor < version.minor) ||
                (this.major === version.major && this.minor === version.minor && this.patch < version.patch))) {
      return -1;
    }
    return 0;
  };

  /**
   * @param {webfont.Version} version
   * @return {boolean}
   */
  Version.prototype.gt = function (version) {
    return this.compare(version) === 1;
  };

  /**
   * @param {webfont.Version} version
   * @return {boolean}
   */
  Version.prototype.lt = function (version) {
    return this.compare(version) === -1;
  };

  /**
   * @param {webfont.Version} version
   * @return {boolean}
   */
  Version.prototype.ge = function (version) {
    return this.compare(version) === 0 || this.compare(version) === 1;
  };

  /**
   * @param {webfont.Version} version
   * @return {boolean}
   */
  Version.prototype.le = function (version) {
    return this.compare(version) === 0 || this.compare(version) === -1;
  };

  /**
   * @param {webfont.Version} version
   * @return {boolean}
   */
  Version.prototype.eq = function (version) {
    return this.compare(version) === 0;
  };

  /**
   * @param {webfont.Version} version
   * @return {boolean}
   */
  Version.prototype.ne = function (version) {
    return this.compare(version) !== 0;
  };

  /**
   * @return {string}
   */
  Version.prototype.toString = function () {
    return [this.major, this.minor || '', this.patch || '', this.build || ''].join('');
  };

  /**
   * @param {string} str
   * @return {!webfont.Version}
   */
  Version.parse = function (str) {
    var match = Version.TOKENIZER.exec(str),
        major = null,
        minor = null,
        patch = null,
        build = null;

    if (match) {
      if (!goog.isNull(match[1]) && !!match[1]) {
        major = parseInt(match[1], 10);
      }

      if (!goog.isNull(match[2]) && !!match[2]) {
        minor = parseInt(match[2], 10);
      }

      if (!goog.isNull(match[3]) && !!match[3]) {
        patch = parseInt(match[3], 10);
      }

      if (!goog.isNull(match[4]) && !!match[4]) {
        if (/^[0-9]+$/.test(match[4])) {
          build = parseInt(match[4], 10);
        } else {
          build = match[4];
        }
      }
    }

    return new Version(major, minor, patch, build);
  };
});

goog.provide('webfont.UserAgent');

/**
 * A user agent string representation.
 *
 * This currently keeps a string and parsed `Version` representation
 * of version strings. This is done for backwards compatibility with
 * older versions of Typekit's KitJS when loaded through the Web Font
 * Loader. The old string based API is deprecated and will eventually
 * be removed.
 *
 * @export
 * @param {string} name
 * @param {webfont.Version} version
 * @param {string} versionString
 * @param {string} engine
 * @param {webfont.Version} engineVersion
 * @param {string} engineVersionString
 * @param {string} platform
 * @param {webfont.Version} platformVersion
 * @param {string} platformVersionString
 * @param {number|undefined} documentMode
 * @param {!webfont.BrowserInfo} browserInfo
 * @constructor
 */
webfont.UserAgent = function(
    name,
    version,
    versionString,
    engine,
    engineVersion,
    engineVersionString,
    platform,
    platformVersion,
    platformVersionString,
    documentMode,
    browserInfo) {
  this.name_ = name;
  this.version_ = version;
  this.versionString_ = versionString;
  this.engine_ = engine;
  this.engineVersion_ = engineVersion;
  this.engineVersionString_ = engineVersionString;
  this.platform_ = platform;
  this.platformVersion_ = platformVersion;
  this.platformVersionString_ = platformVersionString;
  this.documentMode_ = documentMode;
  this.browserInfo_ = browserInfo;
};

goog.scope(function () {
  var UserAgent = webfont.UserAgent;

  /**
   * @export
   * @return {string}
   */
  UserAgent.prototype.getName = function() {
    return this.name_;
  };

  /**
   * @export
   * @deprecated
   * @return {string}
   */
  UserAgent.prototype.getVersion = function() {
    return this.versionString_;
  };

  /**
   * @return {webfont.Version}
   */
  UserAgent.prototype.getParsedVersion = function() {
    return this.version_;
  };

  /**
   * @export
   * @return {string}
   */
  UserAgent.prototype.getEngine = function() {
    return this.engine_;
  };

  /**
   * @export
   * @deprecated
   * @return {string}
   */
  UserAgent.prototype.getEngineVersion = function() {
    return this.engineVersionString_;
  };

  /**
   * @return {webfont.Version}
   */
  UserAgent.prototype.getParsedEngineVersion = function() {
    return this.engineVersion_;
  };

  /**
   * @export
   * @return {string}
   */
  UserAgent.prototype.getPlatform = function() {
    return this.platform_;
  };

  /**
   * @export
   * @deprecated
   * @return {string}
   */
  UserAgent.prototype.getPlatformVersion = function() {
    return this.platformVersionString_;
  };

  /**
   * @return {webfont.Version}
   */
  UserAgent.prototype.getParsedPlatformVersion = function() {
    return this.platformVersion_;
  };

  /**
   * @export
   * @return {number|undefined}
   */
  UserAgent.prototype.getDocumentMode = function() {
    return this.documentMode_;
  };

  /**
   * @export
   * @return {webfont.BrowserInfo}
   */
  UserAgent.prototype.getBrowserInfo = function() {
    return this.browserInfo_;
  };
});

goog.provide('webfont.UserAgentParser');

goog.require('webfont.BrowserInfo');
goog.require('webfont.UserAgent');
goog.require('webfont.Version');

/**
 * @param {string} userAgent The browser userAgent string to parse.
 * @constructor
 */
webfont.UserAgentParser = function(userAgent, doc) {
  this.userAgent_ = userAgent;
  this.doc_ = doc;
};

/**
 * @const
 * @type {string}
 */
webfont.UserAgentParser.UNKNOWN = "Unknown";

/**
 * A constant for identifying a generic browser on a mobile platform that
 * doesn't really have a name, but just came with the platform. Usually these
 * are WebKit based, and examples are the default browser app on Android and
 * the default browser app on BlackBerry 10.
 * @const
 * @type {string}
 */
webfont.UserAgentParser.BUILTIN_BROWSER = "BuiltinBrowser";

/**
 * @const
 * @type {webfont.UserAgent}
 */
webfont.UserAgentParser.UNKNOWN_USER_AGENT = new webfont.UserAgent(
    webfont.UserAgentParser.UNKNOWN,
    new webfont.Version(),
    webfont.UserAgentParser.UNKNOWN,
    webfont.UserAgentParser.UNKNOWN,
    new webfont.Version(),
    webfont.UserAgentParser.UNKNOWN,
    webfont.UserAgentParser.UNKNOWN,
    new webfont.Version(),
    webfont.UserAgentParser.UNKNOWN,
    undefined,
    new webfont.BrowserInfo(false, false, false));


goog.scope(function () {
  var UserAgentParser = webfont.UserAgentParser,
      BrowserInfo = webfont.BrowserInfo,
      UserAgent = webfont.UserAgent,
      Version = webfont.Version;

  /**
   * Parses the user agent string and returns an object.
   * @return {webfont.UserAgent}
   */
  UserAgentParser.prototype.parse = function() {
    if (this.isIe_()) {
      return this.parseIeUserAgentString_();
    } else if (this.isOpera_()) {
      return this.parseOperaUserAgentString_();
    } else if (this.isWebKit_()) {
      return this.parseWebKitUserAgentString_();
    } else if (this.isGecko_()) {
      return this.parseGeckoUserAgentString_();
    } else {
      return webfont.UserAgentParser.UNKNOWN_USER_AGENT;
    }
  };

  /**
   * @private
   */
  UserAgentParser.prototype.getPlatform_ = function() {
    var mobileOs = this.getMatchingGroup_(this.userAgent_,
        /(iPod|iPad|iPhone|Android|Windows Phone|BB\d{2}|BlackBerry)/, 1);

    if (mobileOs != "") {
      if (/BB\d{2}/.test(mobileOs)) {
        mobileOs = "BlackBerry";
      }
      return mobileOs;
    }
    var os = this.getMatchingGroup_(this.userAgent_,
        /(Linux|Mac_PowerPC|Macintosh|Windows|CrOS)/, 1);

    if (os != "") {
      if (os == "Mac_PowerPC") {
        os = "Macintosh";
      }
      return os;
    }
    return webfont.UserAgentParser.UNKNOWN;
  };

  /**
   * @private
   * @return {string}
   */
  UserAgentParser.prototype.getPlatformVersionString_ = function() {
    var genericVersion = this.getMatchingGroup_(this.userAgent_,
        /(OS X|Windows NT|Android) ([^;)]+)/, 2);
    if (genericVersion) {
      return genericVersion;
    }
    var winPhoneVersion = this.getMatchingGroup_(this.userAgent_,
        /Windows Phone( OS)? ([^;)]+)/, 2);
    if (winPhoneVersion) {
      return winPhoneVersion;
    }
    var iVersion = this.getMatchingGroup_(this.userAgent_,
        /(iPhone )?OS ([\d_]+)/, 2);
    if (iVersion) {
      return iVersion;
    }
    var linuxOrCrOsVersion = this.getMatchingGroup_(this.userAgent_,
        /(?:Linux|CrOS) ([^;)]+)/, 1);
    if (linuxOrCrOsVersion) {
      var parts = linuxOrCrOsVersion.split(/\s/);
      for (var i = 0; i < parts.length; i += 1) {
        if (/^[\d\._]+$/.test(parts[i])) {
          return parts[i];
        }
      }
    }
    var blackBerryVersion = this.getMatchingGroup_(this.userAgent_,
        /(BB\d{2}|BlackBerry).*?Version\/([^\s]*)/, 2);
    if (blackBerryVersion) {
      return blackBerryVersion;
    }

    return UserAgentParser.UNKNOWN;
  };

  /**
   * @private
   */
  UserAgentParser.prototype.isIe_ = function() {
    return this.userAgent_.indexOf("MSIE") != -1;
  };

  /**
   * @private
   */
  UserAgentParser.prototype.parseIeUserAgentString_ = function() {
    var platform = this.getPlatform_(),
        platformVersionString = this.getPlatformVersionString_(),
        platformVersion = Version.parse(platformVersionString),
        browserVersionString = this.getMatchingGroup_(this.userAgent_, /MSIE ([\d\w\.]+)/, 1),
        browserVersion = Version.parse(browserVersionString),
        documentMode = this.getDocumentMode_(this.doc_),
        supportWebFont = (platform == "Windows" && browserVersion.major >= 6) ||
                         (platform == "Windows Phone" && platformVersion.major >= 8);

    // For IE we give MSIE as the engine name and the version of IE
    // instead of the specific Trident engine name and version
    return new UserAgent(
      "MSIE",
      browserVersion,
      browserVersionString,
      "MSIE",
      browserVersion,
      browserVersionString,
      platform,
      platformVersion,
      platformVersionString,
      documentMode,
      new BrowserInfo(supportWebFont, false, false)
    );
  };

  /**
   * @private
   */
  UserAgentParser.prototype.isOpera_ = function() {
    return this.userAgent_.indexOf("Opera") != -1;
  };

  /**
   * @private
   */
  UserAgentParser.prototype.parseOperaUserAgentString_ = function() {
    var engineName = UserAgentParser.UNKNOWN,
        engineVersionString = this.getMatchingGroup_(this.userAgent_, /Presto\/([\d\w\.]+)/, 1),
        engineVersion = Version.parse(engineVersionString),
        platformVersionString = this.getPlatformVersionString_(),
        platformVersion = Version.parse(platformVersionString),
        documentMode = this.getDocumentMode_(this.doc_);

    if (engineVersion.isValid()) {
      engineName = "Presto";
    } else {
      if (this.userAgent_.indexOf("Gecko") != -1) {
        engineName = "Gecko";
      }
      engineVersionString = this.getMatchingGroup_(this.userAgent_, /rv:([^\)]+)/, 1);
      engineVersion = Version.parse(engineVersionString);
    }

    // Check for Opera Mini first, since it looks like normal Opera
    if (this.userAgent_.indexOf("Opera Mini/") != -1) {
      var browserVersionString = this.getMatchingGroup_(this.userAgent_, /Opera Mini\/([\d\.]+)/, 1);
      var browserVersion = Version.parse(browserVersionString);

      return new UserAgent(
        "OperaMini",
        browserVersion,
        browserVersionString,
        engineName,
        engineVersion,
        engineVersionString,
        this.getPlatform_(),
        platformVersion,
        platformVersionString,
        documentMode,
        new BrowserInfo(false, false, false)
      );
    }

    // Otherwise, find version information for normal Opera or Opera Mobile
    if (this.userAgent_.indexOf("Version/") != -1) {
      var browserVersionString = this.getMatchingGroup_(this.userAgent_, /Version\/([\d\.]+)/, 1);
      var browserVersion = Version.parse(browserVersionString);

      if (browserVersion.isValid()) {
        return new UserAgent(
          "Opera",
          browserVersion,
          browserVersionString,
          engineName,
          engineVersion,
          engineVersionString,
          this.getPlatform_(),
          platformVersion,
          platformVersionString,
          documentMode,
          new BrowserInfo(browserVersion.major >= 10, false, false)
        );
      }
    }
    var browserVersionString = this.getMatchingGroup_(this.userAgent_, /Opera[\/ ]([\d\.]+)/, 1);
    var browserVersion = Version.parse(browserVersionString);

    if (browserVersion.isValid()) {
      return new UserAgent(
        "Opera",
        browserVersion,
        browserVersionString,
        engineName,
        engineVersion,
        engineVersionString,
        this.getPlatform_(),
        platformVersion,
        platformVersionString,
        documentMode,
        new BrowserInfo(browserVersion.major >= 10, false, false)
      );
    }
    return new UserAgent(
      "Opera",
      new Version(),
      UserAgentParser.UNKNOWN,
      engineName,
      engineVersion,
      engineVersionString,
      this.getPlatform_(),
      platformVersion,
      platformVersionString,
      documentMode,
      new BrowserInfo(false, false, false)
    );
  };

  /**
   * @private
   */
  UserAgentParser.prototype.isWebKit_ = function() {
    return /AppleWeb(K|k)it/.test(this.userAgent_);
  };

  /**
   * @private
   */
  UserAgentParser.prototype.parseWebKitUserAgentString_ = function() {
    var platform = this.getPlatform_(),
        platformVersionString = this.getPlatformVersionString_(),
        platformVersion = Version.parse(platformVersionString),
        webKitVersionString = this.getMatchingGroup_(this.userAgent_, /AppleWeb(?:K|k)it\/([\d\.\+]+)/, 1),
        webKitVersion = Version.parse(webKitVersionString),
        browserName = UserAgentParser.UNKNOWN,
        browserVersion = new Version(),
        browserVersionString = UserAgentParser.UNKNOWN,
        supportWebFont = false;

    if (this.userAgent_.indexOf("Chrome") != -1 ||
        this.userAgent_.indexOf("CrMo") != -1 ||
        this.userAgent_.indexOf("CriOS") != -1) {
      browserName = "Chrome";
    } else if (/Silk\/\d/.test(this.userAgent_)) {
      browserName = "Silk";
    } else if (platform == "BlackBerry" || platform == "Android") {
      browserName = UserAgentParser.BUILTIN_BROWSER;
    } else if (this.userAgent_.indexOf("Safari") != -1) {
      browserName = "Safari";
    } else if (this.userAgent_.indexOf("AdobeAIR") != -1) {
      browserName = "AdobeAIR";
    }

    if (browserName == UserAgentParser.BUILTIN_BROWSER) {
      browserVersionString = UserAgentParser.UNKNOWN;
    } else if (browserName == "Silk") {
      browserVersionString = this.getMatchingGroup_(this.userAgent_, /Silk\/([\d\._]+)/, 1);
    } else if (browserName == "Chrome") {
      browserVersionString = this.getMatchingGroup_(this.userAgent_, /(Chrome|CrMo|CriOS)\/([\d\.]+)/, 2);
    } else if (this.userAgent_.indexOf("Version/") != -1) {
      browserVersionString = this.getMatchingGroup_(this.userAgent_, /Version\/([\d\.\w]+)/, 1);
    } else if (browserName == "AdobeAIR") {
      browserVersionString = this.getMatchingGroup_(this.userAgent_, /AdobeAIR\/([\d\.]+)/, 1);
    }
    browserVersion = Version.parse(browserVersionString);

    if (browserName == "AdobeAIR") {
      supportWebFont = browserVersion.major > 2 || browserVersion.major == 2 && browserVersion.minor >= 5;
    } else if (platform == "BlackBerry") {
      supportWebFont = platformVersion.major >= 10;
    } else if (platform == "Android") {
      supportWebFont = platformVersion.major > 2 || (platformVersion.major == 2 && platformVersion.minor > 1);
    } else {
      supportWebFont = webKitVersion.major >= 526 || webKitVersion.major >= 525 && webKitVersion.minor >= 13;
    }

    var hasWebKitFallbackBug = webKitVersion.major < 536 || (webKitVersion.major == 536 && webKitVersion.minor < 11),
        hasWebKitMetricsBug = platform == 'iPhone' || platform == 'iPad' || platform == 'iPod' || platform == 'Macintosh';

    return new UserAgent(
      browserName,
      browserVersion,
      browserVersionString,
      "AppleWebKit",
      webKitVersion,
      webKitVersionString,
      platform,
      platformVersion,
      platformVersionString,
      this.getDocumentMode_(this.doc_),
      new BrowserInfo(supportWebFont, hasWebKitFallbackBug, hasWebKitMetricsBug)
    );
  };

  /**
   * @private
   */
  UserAgentParser.prototype.isGecko_ = function() {
    return this.userAgent_.indexOf("Gecko") != -1;
  };

  /**
   * @private
   */
  UserAgentParser.prototype.parseGeckoUserAgentString_ = function() {
    var name = UserAgentParser.UNKNOWN,
        version = new Version(),
        versionString = UserAgentParser.UNKNOWN,
        platformVersionString = this.getPlatformVersionString_(),
        platformVersion = Version.parse(platformVersionString),
        supportWebFont = false;

    if (this.userAgent_.indexOf("Firefox") != -1) {
      name = "Firefox";
      versionString = this.getMatchingGroup_(this.userAgent_, /Firefox\/([\d\w\.]+)/, 1);
      version = Version.parse(versionString);
      supportWebFont = version.major >= 3 && version.minor >= 5;
    } else if (this.userAgent_.indexOf("Mozilla") != -1) {
      name = "Mozilla";
    }

    var engineVersionString = this.getMatchingGroup_(this.userAgent_, /rv:([^\)]+)/, 1),
        engineVersion = Version.parse(engineVersionString);

    if (!supportWebFont) {
      supportWebFont = engineVersion.major > 1 ||
                       engineVersion.major == 1 && engineVersion.minor > 9 ||
                       engineVersion.major == 1 && engineVersion.minor == 9 && engineVersion.patch >= 2 ||
                       engineVersionString.match(/1\.9\.1b[123]/) != null ||
                       engineVersionString.match(/1\.9\.1\.[\d\.]+/) != null;
    }
    return new UserAgent(
      name,
      version,
      versionString,
      "Gecko",
      engineVersion,
      engineVersionString,
      this.getPlatform_(),
      platformVersion,
      platformVersionString,
      this.getDocumentMode_(this.doc_),
      new BrowserInfo(supportWebFont, false, false)
    );
  };

  /**
   * @private
   */
  UserAgentParser.prototype.getMatchingGroup_ = function(str,
      regexp, index) {
    var groups = str.match(regexp);

    if (groups && groups[index]) {
      return groups[index];
    }
    return "";
  };

  /**
   * @private
   */
  UserAgentParser.prototype.getDocumentMode_ = function(doc) {
    if (doc.documentMode) return doc.documentMode;
    return undefined;
  };
});

goog.provide('webfont.CssClassName');

/**
 * Handles sanitization and construction of css class names.
 * @param {string=} opt_joinChar The character to join parts of the name on.
 *    Defaults to '-'.
 * @constructor
 */
webfont.CssClassName = function(opt_joinChar) {
  /** @type {string} */
  this.joinChar_ = opt_joinChar || webfont.CssClassName.DEFAULT_JOIN_CHAR;
};

/**
 * @const
 * @type {string}
 */
webfont.CssClassName.DEFAULT_JOIN_CHAR = '-';

goog.scope(function () {
  var CssClassName = webfont.CssClassName;

  /**
   * Sanitizes a string for use as a css class name. Removes non-word and
   * underscore characters.
   * @param {string} name The string.
   * @return {string} The sanitized string.
   */
  CssClassName.prototype.sanitize = function(name) {
    return name.replace(/[\W_]+/g, '').toLowerCase();
  };

  /**
   * Builds a complete css class name given a variable number of parts.
   * Sanitizes, then joins the parts together.
   * @param {...string} var_args The parts to join.
   * @return {string} The sanitized and joined string.
   */
  CssClassName.prototype.build = function(var_args) {
    var parts = []
    for (var i = 0; i < arguments.length; i++) {
      parts.push(this.sanitize(arguments[i]));
    }
    return parts.join(this.joinChar_);
  };
});

goog.provide('webfont.Font');

/**
 * This class is an abstraction for a single font or typeface.
 * It contains the font name and the variation (i.e. style
 * and weight.) A collection Font instances can represent a
 * font family.
 *
 * @constructor
 * @param {string} name The font family name
 * @param {string=} opt_variation A font variation description
 */
webfont.Font = function (name, opt_variation) {
  this.name_ = name;
  this.weight_ = 4;
  this.style_ = 'n'

  var variation = opt_variation || 'n4',
      match = variation.match(/^([nio])([1-9])$/i);

  if (match) {
    this.style_ = match[1];
    this.weight_ = parseInt(match[2], 10);
  }
};

goog.scope(function () {
  var Font = webfont.Font;

  /**
   * @return {string}
   */
  Font.prototype.getName = function () {
    return this.name_;
  };

  /**
   * @return {string}
   */
  Font.prototype.getCssName = function () {
    return this.quote_(this.name_);
  };

  /**
   * @private
   * @param {string} name
   * @return {string}
   */
  Font.prototype.quote_ = function (name) {
    var quoted = [];
    var split = name.split(/,\s*/);
    for (var i = 0; i < split.length; i++) {
      var part = split[i].replace(/['"]/g, '');
      if (part.indexOf(' ') == -1) {
        quoted.push(part);
      } else {
        quoted.push("'" + part + "'");
      }
    }
    return quoted.join(',');
  };

  /**
   * @return {string}
   */
  Font.prototype.getVariation = function () {
    return this.style_ + this.weight_;
  };

  /**
   * @return {string}
   */
  Font.prototype.getCssVariation = function () {
    var style = 'normal',
        weight = this.weight_ + '00';

    if (this.style_ === 'o') {
      style = 'oblique';
    } else if (this.style_ === 'i') {
      style = 'italic';
    }

    return 'font-style:' + style + ';font-weight:' + weight + ';';
  };

  /**
   * Parses a CSS font declaration and returns a font
   * variation description.
   *
   * @param {string} css
   * @return {string}
   */
  Font.parseCssVariation = function (css) {
    var weight = 4,
        style = 'n',
        m = null;

    if (css) {
      m = css.match(/(normal|oblique|italic)/i);

      if (m && m[1]) {
        style = m[1].substr(0, 1).toLowerCase();
      }

      m = css.match(/([1-9]00|normal|bold)/i);

      if (m && m[1]) {
        if (/bold/i.test(m[1])) {
          weight = 7;
        } else if (/[1-9]00/.test(m[1])) {
          weight = parseInt(m[1].substr(0, 1), 10);
        }
      }
    }
    return style + weight;
  }
});

goog.provide('webfont.EventDispatcher');

goog.require('webfont.CssClassName');

/**
 * A class to dispatch events and manage the event class names on an html
 * element that represent the current state of fonts on the page. Active class
 * names always overwrite inactive class names of the same type, while loading
 * class names may be present whenever a font is loading (regardless of if an
 * associated active or inactive class name is also present).
 * @param {webfont.DomHelper} domHelper
 * @param {HTMLElement} htmlElement
 * @param {Object} callbacks
 * @param {string=} opt_namespace
 * @constructor
 */
webfont.EventDispatcher = function(domHelper, htmlElement, callbacks,
    opt_namespace) {
  this.domHelper_ = domHelper;
  this.htmlElement_ = htmlElement;
  this.callbacks_ = callbacks;
  this.namespace_ = opt_namespace || webfont.EventDispatcher.DEFAULT_NAMESPACE;
  this.cssClassName_ = new webfont.CssClassName('-');
};

/**
 * @const
 * @type {string}
 */
webfont.EventDispatcher.DEFAULT_NAMESPACE = 'wf';

/**
 * @const
 * @type {string}
 */
webfont.EventDispatcher.LOADING = 'loading';

/**
 * @const
 * @type {string}
 */
webfont.EventDispatcher.ACTIVE = 'active';

/**
 * @const
 * @type {string}
 */
webfont.EventDispatcher.INACTIVE = 'inactive';

/**
 * @const
 * @type {string}
 */
webfont.EventDispatcher.FONT = 'font';

goog.scope(function () {
  var EventDispatcher = webfont.EventDispatcher;

  /**
   * Dispatch the loading event and append the loading class name.
   */
  EventDispatcher.prototype.dispatchLoading = function() {
    this.domHelper_.appendClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.LOADING));
    this.dispatch_(webfont.EventDispatcher.LOADING);
  };

  /**
   * Dispatch the font loading event and append the font loading class name.
   * @param {webfont.Font} font
   */
  EventDispatcher.prototype.dispatchFontLoading = function(font) {
    this.domHelper_.appendClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.LOADING));
    this.dispatch_(
        webfont.EventDispatcher.FONT + webfont.EventDispatcher.LOADING, font);
  };

  /**
   * Dispatch the font active event, remove the font loading class name, remove
   * the font inactive class name, and append the font active class name.
   * @param {webfont.Font} font
   */
  EventDispatcher.prototype.dispatchFontActive = function(font) {
    this.domHelper_.removeClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.LOADING));
    this.domHelper_.removeClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.INACTIVE));
    this.domHelper_.appendClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.ACTIVE));
    this.dispatch_(
        webfont.EventDispatcher.FONT + webfont.EventDispatcher.ACTIVE, font);
  };

  /**
   * Dispatch the font inactive event, remove the font loading class name, and
   * append the font inactive class name (unless the font active class name is
   * already present).
   * @param {webfont.Font} font
   */
  EventDispatcher.prototype.dispatchFontInactive = function(font) {
    this.domHelper_.removeClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.LOADING));
    var hasFontActive = this.domHelper_.hasClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.ACTIVE));
    if (!hasFontActive) {
      this.domHelper_.appendClassName(this.htmlElement_,
          this.cssClassName_.build(
              this.namespace_, font.getName(), font.getVariation().toString(), webfont.EventDispatcher.INACTIVE));
    }
    this.dispatch_(
        webfont.EventDispatcher.FONT + webfont.EventDispatcher.INACTIVE, font);
  };

  /**
   * Dispatch the inactive event, remove the loading class name, and append the
   * inactive class name (unless the active class name is already present).
   */
  EventDispatcher.prototype.dispatchInactive = function() {
    this.domHelper_.removeClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.LOADING));
    var hasActive = this.domHelper_.hasClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.ACTIVE));
    if (!hasActive) {
      this.domHelper_.appendClassName(this.htmlElement_,
          this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.INACTIVE));
    }
    this.dispatch_(webfont.EventDispatcher.INACTIVE);
  };

  /**
   * Dispatch the active event, remove the loading class name, remove the inactive
   * class name, and append the active class name.
   */
  EventDispatcher.prototype.dispatchActive = function() {
    this.domHelper_.removeClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.LOADING));
    this.domHelper_.removeClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.INACTIVE));
    this.domHelper_.appendClassName(this.htmlElement_,
        this.cssClassName_.build(
            this.namespace_, webfont.EventDispatcher.ACTIVE));
    this.dispatch_(webfont.EventDispatcher.ACTIVE);
  };

  /**
   * @param {string} event
   * @param {webfont.Font=} opt_font
   */
  EventDispatcher.prototype.dispatch_ = function(event, opt_font) {
    if (this.callbacks_[event]) {
      if (opt_font) {
        this.callbacks_[event](opt_font.getName(), opt_font.getVariation());
      } else {
        this.callbacks_[event]();
      }
    }
  };
});

goog.provide('webfont.FontModule');

/**
 * @interface
 */
webfont.FontModule = function () {};

goog.scope(function () {
  var FontModule = webfont.FontModule;

  /**
   * @param {webfont.UserAgent} userAgent
   * @param {function(boolean)} support
   */
  FontModule.prototype.supportUserAgent = function (userAgent, support) {};

  /**
   * @param {function(Array.<webfont.Font>,  webfont.FontTestStrings=, Object.<string, boolean>=)} onReady
   */
  FontModule.prototype.load = function (onReady) {};
});


goog.provide('webfont.FontModuleLoader');
goog.provide('webfont.FontModuleFactory');

/** @typedef {function(Object, webfont.DomHelper): webfont.FontModule} */
webfont.FontModuleFactory;

/**
 * @constructor
 */
webfont.FontModuleLoader = function() {
  /**
   * @type {Object.<string, webfont.FontModuleFactory>}
   */
  this.modules_ = {};
};

goog.scope(function () {
  var FontModuleLoader = webfont.FontModuleLoader;

  /**
   * @param {string} name
   * @param {webfont.FontModuleFactory} factory
   */
  FontModuleLoader.prototype.addModuleFactory = function(name, factory) {
    this.modules_[name] = factory;
  };

  /**
   * @param {Object} configuration
   * @param {webfont.DomHelper} domHelper
   * @return {Array.<webfont.FontModule>}
   */
  FontModuleLoader.prototype.getModules = function(configuration, domHelper) {
    var modules = [];

    for (var key in configuration) {
      if (configuration.hasOwnProperty(key)) {
        var moduleFactory = this.modules_[key];

        if (moduleFactory) {
          modules.push(moduleFactory(configuration[key], domHelper));
        }
      }
    }
    return modules;
  };
});

goog.provide('webfont.FontRuler');

/**
 * An element that can be used to measure the metrics
 * of a given font and string.
 * @constructor
 * @param {webfont.DomHelper} domHelper
 * @param {string} fontTestString
 */
webfont.FontRuler = function (domHelper, fontTestString) {
  this.domHelper_ = domHelper;
  this.fontTestString_ = fontTestString;
  this.el_ = this.domHelper_.createElement('span', {
    "aria-hidden": "true"
  }, this.fontTestString_);
};

goog.scope(function () {
  var FontRuler = webfont.FontRuler;

  /**
   * @param {webfont.Font} font
   */
  FontRuler.prototype.setFont = function(font) {
    this.domHelper_.setStyle(this.el_, this.computeStyleString_(font));
  };

  /**
   * Inserts the ruler into the DOM.
   */
  FontRuler.prototype.insert = function() {
    this.domHelper_.insertInto('body', this.el_);
  };

  /**
   * @private
   * @param {webfont.Font} font
   * @return {string}
   */
  FontRuler.prototype.computeStyleString_ = function(font) {
    return "position:absolute;top:-999px;left:-999px;" +
           "font-size:300px;width:auto;height:auto;line-height:normal;margin:0;" +
           "padding:0;font-variant:normal;white-space:nowrap;font-family:" +
           font.getCssName() + ";" + font.getCssVariation();
  };

  /**
   * @return {number}
   */
  FontRuler.prototype.getWidth = function() {
    return this.el_.offsetWidth;
  };

  /**
   * Removes the ruler element from the DOM.
   */
  FontRuler.prototype.remove = function() {
    this.domHelper_.removeElement(this.el_);
  };
});

goog.provide('webfont.FontWatchRunner');

goog.require('webfont.Font');
goog.require('webfont.FontRuler');

/**
 * @constructor
 * @param {function(webfont.Font)} activeCallback
 * @param {function(webfont.Font)} inactiveCallback
 * @param {webfont.DomHelper} domHelper
 * @param {webfont.Font} font
 * @param {webfont.BrowserInfo} browserInfo
 * @param {number=} opt_timeout
 * @param {Object.<string, boolean>=} opt_metricCompatibleFonts
 * @param {string=} opt_fontTestString
 */
webfont.FontWatchRunner = function(activeCallback, inactiveCallback, domHelper,
    font, browserInfo, opt_timeout, opt_metricCompatibleFonts, opt_fontTestString) {
  this.activeCallback_ = activeCallback;
  this.inactiveCallback_ = inactiveCallback;
  this.domHelper_ = domHelper;
  this.font_ = font;
  this.fontTestString_ = opt_fontTestString || webfont.FontWatchRunner.DEFAULT_TEST_STRING;
  this.browserInfo_ = browserInfo;
  this.lastResortWidths_ = {};
  this.timeout_ = opt_timeout || 5000;

  this.metricCompatibleFonts_ = opt_metricCompatibleFonts || null;

  this.fontRulerA_ = null;
  this.fontRulerB_ = null;

  this.setupLastResortWidths_();
};

/**
 * @enum {string}
 * @const
 */
webfont.FontWatchRunner.LastResortFonts = {
  SERIF: 'serif',
  SANS_SERIF: 'sans-serif',
  MONOSPACE: 'monospace'
};

/**
 * Default test string. Characters are chosen so that their widths vary a lot
 * between the fonts in the default stacks. We want each fallback stack
 * to always start out at a different width than the other.
 * @type {string}
 * @const
 */
webfont.FontWatchRunner.DEFAULT_TEST_STRING = 'BESbswy';

goog.scope(function () {
  var FontWatchRunner = webfont.FontWatchRunner,
      Font = webfont.Font,
      FontRuler = webfont.FontRuler;

  /**
   * @private
   */
  FontWatchRunner.prototype.setupLastResortWidths_ = function() {
    var fontRuler = new FontRuler(this.domHelper_, this.fontTestString_);

    fontRuler.insert();

    for (var font in FontWatchRunner.LastResortFonts) {
      if (FontWatchRunner.LastResortFonts.hasOwnProperty(font)) {
        fontRuler.setFont(new Font(FontWatchRunner.LastResortFonts[font], this.font_.getVariation()));
        this.lastResortWidths_[FontWatchRunner.LastResortFonts[font]] = fontRuler.getWidth();
      }
    }
    fontRuler.remove();
  };

  FontWatchRunner.prototype.start = function() {
    this.fontRulerA_ = new FontRuler(this.domHelper_, this.fontTestString_);
    this.fontRulerA_.insert();
    this.fontRulerB_ = new FontRuler(this.domHelper_, this.fontTestString_);
    this.fontRulerB_.insert();

    this.started_ = goog.now();

    this.fontRulerA_.setFont(new Font(this.font_.getName() + ',' + FontWatchRunner.LastResortFonts.SERIF, this.font_.getVariation()));
    this.fontRulerB_.setFont(new Font(this.font_.getName() + ',' + FontWatchRunner.LastResortFonts.SANS_SERIF, this.font_.getVariation()));

    this.check_();
  };

  /**
   * Returns true if the given width matches the generic font family width.
   *
   * @private
   * @param {number} width
   * @param {string} lastResortFont
   * @return {boolean}
   */
  FontWatchRunner.prototype.widthMatches_ = function(width, lastResortFont) {
    return width === this.lastResortWidths_[lastResortFont];
  };

  /**
   * Return true if the given widths match any of the generic font family
   * widths.
   *
   * @private
   * @param {number} a
   * @param {number} b
   * @return {boolean}
   */
  FontWatchRunner.prototype.widthsMatchLastResortWidths_ = function(a, b) {
    for (var font in FontWatchRunner.LastResortFonts) {
      if (FontWatchRunner.LastResortFonts.hasOwnProperty(font)) {
        if (this.widthMatches_(a, FontWatchRunner.LastResortFonts[font]) &&
            this.widthMatches_(b, FontWatchRunner.LastResortFonts[font])) {
          return true;
        }
      }
    }
    return false;
  };

  /**
   * @private
   * Returns true if the loading has timed out.
   * @return {boolean}
   */
  FontWatchRunner.prototype.hasTimedOut_ = function() {
    return goog.now() - this.started_ >= this.timeout_;
  };

  /**
   * Returns true if both fonts match the normal fallback fonts.
   *
   * @private
   * @param {number} a
   * @param {number} b
   * @return {boolean}
   */
  FontWatchRunner.prototype.isFallbackFont_ = function (a, b) {
    return this.widthMatches_(a, FontWatchRunner.LastResortFonts.SERIF) &&
           this.widthMatches_(b, FontWatchRunner.LastResortFonts.SANS_SERIF);
  };

  /**
   * Returns true if the WebKit bug is present and both widths match a last resort font.
   *
   * @private
   * @param {number} a
   * @param {number} b
   * @return {boolean}
   */
  FontWatchRunner.prototype.isLastResortFont_ = function (a, b) {
    return this.browserInfo_.hasWebKitFallbackBug() && this.widthsMatchLastResortWidths_(a, b);
  };

  /**
   * Returns true if the current font is metric compatible. Also returns true
   * if we do not have a list of metric compatible fonts.
   *
   * @private
   * @return {boolean}
   */
  FontWatchRunner.prototype.isMetricCompatibleFont_ = function () {
    return this.metricCompatibleFonts_ === null || this.metricCompatibleFonts_.hasOwnProperty(this.font_.getName());
  };

  /**
   * Checks the width of the two spans against their original widths during each
   * async loop. If the width of one of the spans is different than the original
   * width, then we know that the font is rendering and finish with the active
   * callback. If we wait more than 5 seconds and nothing has changed, we finish
   * with the inactive callback.
   *
   * @private
   */
  FontWatchRunner.prototype.check_ = function() {
    var widthA = this.fontRulerA_.getWidth();
    var widthB = this.fontRulerB_.getWidth();

    if (this.isFallbackFont_(widthA, widthB) || this.isLastResortFont_(widthA, widthB)) {
      if (this.hasTimedOut_()) {
        if (this.isLastResortFont_(widthA, widthB) && this.isMetricCompatibleFont_()) {
          this.finish_(this.activeCallback_);
        } else {
          this.finish_(this.inactiveCallback_);
        }
     } else {
        this.asyncCheck_();
      }
    } else {
      this.finish_(this.activeCallback_);
    }
  };

  /**
   * @private
   */
  FontWatchRunner.prototype.asyncCheck_ = function() {
    setTimeout(goog.bind(function () {
      this.check_();
    }, this), 25);
  };

  /**
   * @private
   * @param {function(webfont.Font)} callback
   */
  FontWatchRunner.prototype.finish_ = function(callback) {
    this.fontRulerA_.remove();
    this.fontRulerB_.remove();
    callback(this.font_);
  };
});

goog.provide('webfont.FontWatcher');

goog.require('webfont.FontWatchRunner');

/**
 * @constructor
 * @param {webfont.UserAgent} userAgent
 * @param {webfont.DomHelper} domHelper
 * @param {webfont.EventDispatcher} eventDispatcher
 * @param {number=} opt_timeout
 */
webfont.FontWatcher = function(userAgent, domHelper, eventDispatcher, opt_timeout) {
  this.domHelper_ = domHelper;
  this.eventDispatcher_ = eventDispatcher;
  this.currentlyWatched_ = 0;
  this.last_ = false;
  this.success_ = false;
  this.timeout_ = opt_timeout;

  this.browserInfo_ = userAgent.getBrowserInfo();
};

goog.scope(function () {
  var FontWatcher = webfont.FontWatcher,
      FontWatchRunner = webfont.FontWatchRunner;

  /**
   * Watches a set of font families.
   * @param {Array.<webfont.Font>} fonts The fonts to watch.
   * @param {Object.<string, string>} fontTestStrings The font test strings for
   *     each family.
   * @param {Object.<String, boolean>} metricCompatibleFonts
   * @param {boolean} last True if this is the last set of fonts to watch.
   */
  FontWatcher.prototype.watchFonts = function(fonts,
      fontTestStrings, metricCompatibleFonts, last) {
    var length = fonts.length;

    if (length === 0 && last) {
      this.eventDispatcher_.dispatchInactive();
      return;
    }

    this.currentlyWatched_ += fonts.length;

    if (last) {
      this.last_ = last;
    }

    for (var i = 0; i < fonts.length; i++) {
      var font = fonts[i],
          fontTestString = fontTestStrings[font.getName()];

      this.eventDispatcher_.dispatchFontLoading(font);

      var fontWatchRunner = new FontWatchRunner(
            goog.bind(this.fontActive_, this),
            goog.bind(this.fontInactive_, this),
            this.domHelper_,
            font,
            this.browserInfo_,
            this.timeout_,
            metricCompatibleFonts,
            fontTestString
          );

      fontWatchRunner.start();
    }
  };

  /**
   * Called by a FontWatchRunner when a font has been detected as active.
   * @param {webfont.Font} font
   * @private
   */
  FontWatcher.prototype.fontActive_ = function(font) {
    this.eventDispatcher_.dispatchFontActive(font);
    this.success_ = true;
    this.decreaseCurrentlyWatched_();
  };

  /**
   * Called by a FontWatchRunner when a font has been detected as inactive.
   * @param {webfont.Font} font
   * @private
   */
  FontWatcher.prototype.fontInactive_ = function(font) {
    this.eventDispatcher_.dispatchFontInactive(font);
    this.decreaseCurrentlyWatched_();
  };

  /**
   * @private
   */
  FontWatcher.prototype.decreaseCurrentlyWatched_ = function() {
    if (--this.currentlyWatched_ == 0 && this.last_) {
      if (this.success_) {
        this.eventDispatcher_.dispatchActive();
      } else {
        this.eventDispatcher_.dispatchInactive();
      }
    }
  };
});

goog.provide('webfont.WebFont');

goog.require('webfont.DomHelper');
goog.require('webfont.EventDispatcher');
goog.require('webfont.FontWatcher');

/**
 * @param {Window} mainWindow The main application window containing
 *   webfontloader.js.
 * @param {webfont.FontModuleLoader} fontModuleLoader A loader instance to use.
 * @param {webfont.UserAgent} userAgent The detected user agent to load for.
 * @constructor
 */
webfont.WebFont = function(mainWindow, fontModuleLoader, userAgent) {
  this.mainWindow_ = mainWindow;
  this.fontModuleLoader_ = fontModuleLoader;
  this.userAgent_ = userAgent;
  this.moduleLoading_ = 0;
  this.moduleFailedLoading_ = 0;
};

goog.scope(function () {
  var WebFont = webfont.WebFont,
      DomHelper = webfont.DomHelper,
      EventDispatcher = webfont.EventDispatcher,
      FontWatcher = webfont.FontWatcher;

  /**
   * @param {string} name
   * @param {webfont.FontModuleFactory} factory
   */
  WebFont.prototype.addModule = function(name, factory) {
    this.fontModuleLoader_.addModuleFactory(name, factory);
  };

  /**
   * @param {Object} configuration
   */
  WebFont.prototype.load = function(configuration) {
    var context = configuration['context'] || this.mainWindow_;
    this.domHelper_ = new DomHelper(this.mainWindow_, context);

    var eventDispatcher = new EventDispatcher(
        this.domHelper_, context.document.documentElement, configuration);

    if (this.userAgent_.getBrowserInfo().hasWebFontSupport()) {
      this.load_(eventDispatcher, configuration);
    } else {
      eventDispatcher.dispatchInactive();
    }
  };

  /**
   * @param {webfont.FontModule} module
   * @param {webfont.EventDispatcher} eventDispatcher
   * @param {webfont.FontWatcher} fontWatcher
   * @param {boolean} support
   */
  WebFont.prototype.isModuleSupportingUserAgent_ = function(module, eventDispatcher,
      fontWatcher, support) {
    var that = this;

    if (!support) {
      var allModulesLoaded = --this.moduleLoading_ == 0;

      this.moduleFailedLoading_--;
      if (allModulesLoaded) {
        if (this.moduleFailedLoading_ == 0) {
          eventDispatcher.dispatchInactive();
        } else {
          eventDispatcher.dispatchLoading();
        }
      }
      fontWatcher.watchFonts([], {}, null, allModulesLoaded);
      return;
    }

    module.load(function (fonts, opt_fontTestStrings, opt_metricCompatibleFonts) {
      that.onModuleReady_(eventDispatcher, fontWatcher, fonts, opt_fontTestStrings, opt_metricCompatibleFonts);
    });
  };

  /**
   * @param {webfont.EventDispatcher} eventDispatcher
   * @param {webfont.FontWatcher} fontWatcher
   * @param {Array.<webfont.Font>} fonts
   * @param {webfont.FontTestStrings=} opt_fontTestStrings
   * @param {Object.<string, boolean>=} opt_metricCompatibleFonts
   */
  WebFont.prototype.onModuleReady_ = function(eventDispatcher, fontWatcher, fonts, opt_fontTestStrings, opt_metricCompatibleFonts) {
    var allModulesLoaded = --this.moduleLoading_ == 0;

    if (allModulesLoaded) {
      eventDispatcher.dispatchLoading();
    }

    setTimeout(function () {
      fontWatcher.watchFonts(fonts, opt_fontTestStrings || {}, opt_metricCompatibleFonts || null, allModulesLoaded);
    }, 0);
  };

  /**
   * @param {webfont.EventDispatcher} eventDispatcher
   * @param {Object} configuration
   */
  WebFont.prototype.load_ = function(eventDispatcher, configuration) {
    var modules = this.fontModuleLoader_.getModules(configuration, this.domHelper_),
        timeout = configuration['timeout'],
        self = this;

    this.moduleFailedLoading_ = this.moduleLoading_ = modules.length;

    var fontWatcher = new webfont.FontWatcher(this.userAgent_, this.domHelper_, eventDispatcher, timeout);

    for (var i = 0, len = modules.length; i < len; i++) {
      var module = modules[i];

      module.supportUserAgent(this.userAgent_,
          goog.bind(this.isModuleSupportingUserAgent_, this, module,
          eventDispatcher, fontWatcher));
    }
  };
});

goog.provide('webfont');

goog.require('webfont.UserAgentParser');
goog.require('webfont.FontModuleLoader');
goog.require('webfont.WebFont');

/**
 * @typedef {Object.<string, Array.<string>>}
 */
webfont.FontTestStrings;

/**
 * Name of the global object
 *
 * @define {string}
 */
var GLOBAL_NAME = 'WebFont';

// Provide an instance of WebFont in the global namespace.
var globalNamespaceObject = window[GLOBAL_NAME] = (function() {
  var userAgentParser = new webfont.UserAgentParser(navigator.userAgent, document);
  var userAgent = userAgentParser.parse();
  var fontModuleLoader = new webfont.FontModuleLoader();
  return new webfont.WebFont(window, fontModuleLoader, userAgent);
})();

// Export the public API.
globalNamespaceObject['load'] = globalNamespaceObject.load;

goog.provide('webfont.modules.Custom');

goog.require('webfont.Font');

/**
 *
 * WebFont.load({
 *   custom: {
 *     families: ['Font1', 'Font2'],
 *    urls: [ 'http://moo', 'http://meuh' ] }
 * });
 *
 * @constructor
 * @implements {webfont.FontModule}
 */
webfont.modules.Custom = function(domHelper, configuration) {
  this.domHelper_ = domHelper;
  this.configuration_ = configuration;
};

webfont.modules.Custom.NAME = 'custom';

goog.scope(function () {
  var Custom = webfont.modules.Custom,
      Font = webfont.Font;

  Custom.prototype.load = function(onReady) {
    var i, len;
    var urls = this.configuration_['urls'] || [];
    var familiesConfiguration = this.configuration_['families'] || [];

    for (i = 0, len = urls.length; i < len; i++) {
      var url = urls[i];

      this.domHelper_.insertInto('head', this.domHelper_.createCssLink(url));
    }

    var fonts = [];

    for (i = 0, len = familiesConfiguration.length; i < len; i++) {
      var components = familiesConfiguration[i].split(":");

      if (components[1]) {
        var variations = components[1].split(",");

        for (var j = 0; j < variations.length; j += 1) {
          fonts.push(new Font(components[0], variations[j]));
        }
      } else {
        fonts.push(new Font(components[0]));
      }
    }

    onReady(fonts);
  };

  Custom.prototype.supportUserAgent = function(userAgent, support) {
    return support(userAgent.getBrowserInfo().hasWebFontSupport());
  };
});

globalNamespaceObject.addModule(webfont.modules.Custom.NAME, function(configuration, domHelper) {
  return new webfont.modules.Custom(domHelper, configuration);
});

goog.provide('webfont.modules.Ascender');

goog.require('webfont.Font');

/**
 *
 * WebFont.load({
 *   ascender: {
 *     key:'ec2de397-11ae-4c10-937f-bf94283a70c1',
 *     families:['AyitaPro:regular,bold,bolditalic,italic']
 *   }
 * });
 *
 * @constructor
 * @implements {webfont.FontModule}
 */
webfont.modules.Ascender = function(domHelper, configuration) {
  this.domHelper_ = domHelper;
  this.configuration_ = configuration;
};

webfont.modules.Ascender.NAME = 'ascender';

webfont.modules.Ascender.VARIATIONS = {
  'regular': 'n4',
  'bold': 'n7',
  'italic': 'i4',
  'bolditalic': 'i7',
  'r': 'n4',
  'b': 'n7',
  'i': 'i4',
  'bi': 'i7'
};

goog.scope(function () {
  var Ascender = webfont.modules.Ascender,
      Font = webfont.Font;

  Ascender.prototype.supportUserAgent = function(userAgent, support) {
    return support(userAgent.getBrowserInfo().hasWebFontSupport());
  };

  Ascender.prototype.load = function(onReady) {
    var key = this.configuration_['key'];
    var protocol = this.domHelper_.getProtocol();
    var url = protocol + '//webfonts.fontslive.com/css/' + key + '.css';
    this.domHelper_.insertInto('head', this.domHelper_.createCssLink(url));
    var fv = this.parseFamiliesAndVariations(this.configuration_['families']);
    onReady(fv);
  };

  /**
   * @param {Array.<string>} providedFamilies
   * @return {Array.<webfont.Font>}
   */
  Ascender.prototype.parseFamiliesAndVariations = function (providedFamilies) {
    var fonts = [];

    for (var i = 0, len = providedFamilies.length; i < len; i++) {
      fonts.push.apply(fonts, this.parseFamilyAndVariations(providedFamilies[i]));
    }
    return fonts;
  };

  /**
   * @param {string} providedFamily
   * @return {Array.<webfont.Font>}
   */
  Ascender.prototype.parseFamilyAndVariations = function (providedFamily){
    var parts = providedFamily.split(':'),
        familyName = parts[0];

    if (parts[1]) {
      var variations = this.parseVariations(parts[1]),
          result = [];

      for (var i = 0; i < variations.length; i += 1) {
        result.push(new Font(familyName, variations[i]));
      }
      return result;
    }
    return [new Font(familyName)];
  };

  /**
   * @param {string} source
   * @return {Array.<string>}
   */
  Ascender.prototype.parseVariations = function (source) {
    var providedVariations = source.split(','),
        variations = [];

    for (var i = 0, len = providedVariations.length; i < len; i++){
      var pv = providedVariations[i];

      if (pv) {
        var v = Ascender.VARIATIONS[pv];
        variations.push(v ? v : pv);
      }
    }
    return variations;
  };
});

globalNamespaceObject.addModule(webfont.modules.Ascender.NAME, function(configuration, domHelper) {
  return new webfont.modules.Ascender(domHelper, configuration);
});

goog.provide('webfont.modules.Monotype');

goog.require('webfont.Font');

/**
webfont.load({
monotype: {
projectId: 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'//this is your Fonts.com Web fonts projectId
}
});
*/

/**
 * @constructor
 * @implements {webfont.FontModule}
 */
webfont.modules.Monotype = function (userAgent, domHelper, configuration) {
  this.userAgent_ = userAgent;
  this.domHelper_ = domHelper;
  this.configuration_ = configuration;
  this.fonts_ = [];
};

/**
 * name of the module through which external API is supposed to call the MonotypeFontAPI.
 * @const
 */
webfont.modules.Monotype.NAME = 'monotype';

/**
 * __mti_fntLst is the name of function that exposes Monotype's font list.
 * @const
 */
webfont.modules.Monotype.HOOK = '__mti_fntLst';

/**
 * __MonotypeAPIScript__ is the id of script added by google API. Currently 'webfonts.fonts.com' supports only one script in a page.
 * This may require change in future if 'webfonts.fonts.com' begins supporting multiple scripts per page.
 * @const
 */
webfont.modules.Monotype.SCRIPTID = '__MonotypeAPIScript__';

goog.scope(function () {
  var Monotype = webfont.modules.Monotype,
      Font = webfont.Font;

  Monotype.prototype.supportUserAgent = function (userAgent, support) {
    var self = this;
    var projectId = self.configuration_['projectId'];
    var version = self.configuration_['version'];
    if (projectId) {
      var loadWindow = self.domHelper_.getLoadWindow(),
          sc = self.domHelper_.createElement("script");

      sc["id"] = Monotype.SCRIPTID + projectId;

      function onload() {
        if (loadWindow[Monotype.HOOK + projectId]) {
          var mti_fnts = loadWindow[Monotype.HOOK + projectId]();
          if (mti_fnts) {
            for (var i = 0; i < mti_fnts.length; i++) {
              self.fonts_.push(new Font(mti_fnts[i]["fontfamily"]));
            }
          }
        }
        support(userAgent.getBrowserInfo().hasWebFontSupport());
      }

      var done = false;

      sc["onload"] = sc["onreadystatechange"] = function () {
        if (!done && (!this["readyState"] || this["readyState"] === "loaded" || this["readyState"] === "complete")) {
          done = true;
          onload();
          sc["onload"] = sc["onreadystatechange"] = null;
        }
      };

      sc["src"] = self.getScriptSrc(projectId, version);
      this.domHelper_.insertInto('head', sc);
    }
    else {
      support(true);
    }
  };

  Monotype.prototype.getScriptSrc = function (projectId, version) {
    var p = this.domHelper_.getProtocol();
    var api = (this.configuration_['api'] || 'fast.fonts.com/jsapi').replace(/^.*http(s?):(\/\/)?/, "");
    return p + "//" + api + '/' + projectId + '.js' + ( version ? '?v='+ version : '' );
  };

  Monotype.prototype.load = function (onReady) {
    onReady(this.fonts_);
  };
});

globalNamespaceObject.addModule(webfont.modules.Monotype.NAME, function (configuration, domHelper) {
  var userAgentParser = new webfont.UserAgentParser(navigator.userAgent, document);
  var userAgent = userAgentParser.parse();
  return new webfont.modules.Monotype(userAgent, domHelper, configuration);
});

goog.provide('webfont.modules.Typekit');

goog.require('webfont.Font');

/**
 * @constructor
 * @implements {webfont.FontModule}
 */
webfont.modules.Typekit = function(domHelper, configuration) {
  this.domHelper_ = domHelper;
  this.configuration_ = configuration;
  this.fonts_ = [];
};

webfont.modules.Typekit.NAME = 'typekit';
webfont.modules.Typekit.HOOK = '__webfonttypekitmodule__';

goog.scope(function () {
  var Typekit = webfont.modules.Typekit,
      Font = webfont.Font;

  Typekit.prototype.getScriptSrc = function(kitId) {
    var protocol = this.domHelper_.getProtocol();
    var api = this.configuration_['api'] || protocol + '//use.typekit.net';
    return api + '/' + kitId + '.js';
  };

  Typekit.prototype.supportUserAgent = function(userAgent, support) {
    var kitId = this.configuration_['id'];
    var configuration = this.configuration_;
    var loadWindow = this.domHelper_.getLoadWindow();
    var self = this;

    if (kitId) {
      // Provide data to Typekit for processing.main
      if (!loadWindow[Typekit.HOOK]) {
        loadWindow[Typekit.HOOK] = {};
      }

      // Typekit will call 'init' to indicate whether it supports fonts
      // and what fonts will be provided.
      loadWindow[Typekit.HOOK][kitId] = function(callback) {
        var init = function(typekitSupports, fontFamilies, fontVariations) {
          for (var i = 0; i < fontFamilies.length; i += 1) {
            var variations = fontVariations[fontFamilies[i]];

            if (variations) {
              for(var j = 0; j < variations.length; j += 1) {
                self.fonts_.push(new Font(fontFamilies[i], variations[j]));
              }
            } else {
              self.fonts_.push(new Font(fontFamilies[i]));
            }
          }
          support(typekitSupports);
        };
        callback(userAgent, configuration, init);
      };

      // Load the Typekit script.
      var script = this.domHelper_.createScriptSrc(this.getScriptSrc(kitId))
      this.domHelper_.insertInto('head', script);
    } else {
      support(true);
    }
  };

  Typekit.prototype.load = function(onReady) {
    onReady(this.fonts_);
  };
});

globalNamespaceObject.addModule(webfont.modules.Typekit.NAME, function(configuration, domHelper) {
  return new webfont.modules.Typekit(domHelper, configuration);
});

goog.provide('webfont.modules.google.FontApiUrlBuilder');

/**
 * @constructor
 */
webfont.modules.google.FontApiUrlBuilder = function(apiUrl, protocol, text) {
  if (apiUrl) {
    this.apiUrl_ = apiUrl;
  } else {
    this.apiUrl_ = protocol + webfont.modules.google.FontApiUrlBuilder.DEFAULT_API_URL;
  }
  this.fontFamilies_ = [];
  this.subsets_ = [];
  this.text_ = text || '';
};


webfont.modules.google.FontApiUrlBuilder.DEFAULT_API_URL = '//fonts.googleapis.com/css';

goog.scope(function () {
  var FontApiUrlBuilder = webfont.modules.google.FontApiUrlBuilder;

  FontApiUrlBuilder.prototype.setFontFamilies = function(fontFamilies) {
    this.parseFontFamilies_(fontFamilies);
  };


  FontApiUrlBuilder.prototype.parseFontFamilies_ =
      function(fontFamilies) {
    var length = fontFamilies.length;

    for (var i = 0; i < length; i++) {
      var elements = fontFamilies[i].split(':');

      if (elements.length == 3) {
        this.subsets_.push(elements.pop());
      }
      var joinCharacter = '';
      if (elements.length == 2 && elements[1] != ''){
        joinCharacter = ':';
      }
      this.fontFamilies_.push(elements.join(joinCharacter));
    }
  };


  FontApiUrlBuilder.prototype.webSafe = function(string) {
    return string.replace(/ /g, '+');
  };


  FontApiUrlBuilder.prototype.build = function() {
    if (this.fontFamilies_.length == 0) {
      throw new Error('No fonts to load !');
    }
    if (this.apiUrl_.indexOf("kit=") != -1) {
      return this.apiUrl_;
    }
    var length = this.fontFamilies_.length;
    var sb = [];

    for (var i = 0; i < length; i++) {
      sb.push(this.webSafe(this.fontFamilies_[i]));
    }
    var url = this.apiUrl_ + '?family=' + sb.join('%7C'); // '|' escaped.

    if (this.subsets_.length > 0) {
      url += '&subset=' + this.subsets_.join(',');
    }

    if (this.text_.length > 0) {
      url += '&text=' + encodeURIComponent(this.text_);
    }

    return url;
  };
});

goog.provide('webfont.modules.google.FontApiParser');

goog.require('webfont.Font');

/**
 * @constructor
 */
webfont.modules.google.FontApiParser = function(fontFamilies) {
  this.fontFamilies_ = fontFamilies;
  this.parsedFonts_ = [];
  this.fontTestStrings_ = {};
};


webfont.modules.google.FontApiParser.INT_FONTS = {
  'latin': webfont.FontWatchRunner.DEFAULT_TEST_STRING,
  'cyrillic': '&#1081;&#1103;&#1046;',
  'greek': '&#945;&#946;&#931;',
  'khmer': '&#x1780;&#x1781;&#x1782;',
  'Hanuman': '&#x1780;&#x1781;&#x1782;' // For backward compatibility
};

webfont.modules.google.FontApiParser.WEIGHTS = {
  'thin': '1',
  'extralight': '2',
  'extra-light': '2',
  'ultralight': '2',
  'ultra-light': '2',
  'light': '3',
  'regular': '4',
  'book': '4',
  'medium': '5',
  'semi-bold': '6',
  'semibold': '6',
  'demi-bold': '6',
  'demibold': '6',
  'bold': '7',
  'extra-bold': '8',
  'extrabold': '8',
  'ultra-bold': '8',
  'ultrabold': '8',
  'black': '9',
  'heavy': '9',
  'l': '3',
  'r': '4',
  'b': '7'
};

webfont.modules.google.FontApiParser.STYLES = {
  'i': 'i',
  'italic': 'i',
  'n': 'n',
  'normal': 'n'
};

webfont.modules.google.FontApiParser.VARIATION_MATCH =
    new RegExp("^(thin|(?:(?:extra|ultra)-?)?light|regular|book|medium|" +
        "(?:(?:semi|demi|extra|ultra)-?)?bold|black|heavy|l|r|b|[1-9]00)?(n|i" +
        "|normal|italic)?$");

goog.scope(function () {
  var FontApiParser = webfont.modules.google.FontApiParser,
      Font = webfont.Font;

  FontApiParser.prototype.parse = function() {
    var length = this.fontFamilies_.length;

    for (var i = 0; i < length; i++) {
      var elements = this.fontFamilies_[i].split(":");
      var fontFamily = elements[0].replace(/\+/g, " ");
      var variations = ['n4'];

      if (elements.length >= 2) {
        var fvds = this.parseVariations_(elements[1]);

        if (fvds.length > 0) {
          variations = fvds;
        }
        if (elements.length == 3) {
          var subsets = this.parseSubsets_(elements[2]);
          if (subsets.length > 0) {
            var fontTestString = FontApiParser.INT_FONTS[subsets[0]];

            if (fontTestString) {
              this.fontTestStrings_[fontFamily] = fontTestString;
            }
          }
        }
      }

      // For backward compatibility
      if (!this.fontTestStrings_[fontFamily]) {
        var hanumanTestString = FontApiParser.INT_FONTS[fontFamily];
        if (hanumanTestString) {
          this.fontTestStrings_[fontFamily] = hanumanTestString;
        }
      }

      for (var j = 0; j < variations.length; j += 1) {
        this.parsedFonts_.push(new Font(fontFamily, variations[j]));
      }
    }
  };

  FontApiParser.prototype.generateFontVariationDescription_ = function(variation) {
    if (!variation.match(/^[\w]+$/)) {
      return '';
    }
    var normalizedVariation = variation.toLowerCase();
    var groups = FontApiParser.VARIATION_MATCH.exec(normalizedVariation);
    if (groups == null) {
      return '';
    }
    var styleMatch = this.normalizeStyle_(groups[2]);
    var weightMatch = this.normalizeWeight_(groups[1]);
    return [styleMatch, weightMatch].join('');
  };


  FontApiParser.prototype.normalizeStyle_ = function(parsedStyle) {
    if (parsedStyle == null || parsedStyle == '') {
      return 'n';
    }
    return FontApiParser.STYLES[parsedStyle];
  };


  FontApiParser.prototype.normalizeWeight_ = function(parsedWeight) {
    if (parsedWeight == null || parsedWeight == '') {
      return '4';
    }
    var weight = FontApiParser.WEIGHTS[parsedWeight];
    if (weight) {
      return weight;
    }
    if (isNaN(parsedWeight)) {
      return '4';
    }
    return parsedWeight.substr(0, 1);
  };


  FontApiParser.prototype.parseVariations_ = function(variations) {
    var finalVariations = [];

    if (!variations) {
      return finalVariations;
    }
    var providedVariations = variations.split(",");
    var length = providedVariations.length;

    for (var i = 0; i < length; i++) {
      var variation = providedVariations[i];
      var fvd = this.generateFontVariationDescription_(variation);

      if (fvd) {
        finalVariations.push(fvd);
      }
    }
    return finalVariations;
  };


  FontApiParser.prototype.parseSubsets_ = function(subsets) {
    var finalSubsets = [];

    if (!subsets) {
      return finalSubsets;
    }
    return subsets.split(",");
  };


  FontApiParser.prototype.getFonts = function() {
    return this.parsedFonts_;
  };

  FontApiParser.prototype.getFontTestStrings = function() {
    return this.fontTestStrings_;
  };
});

goog.provide('webfont.modules.google.GoogleFontApi');

goog.require('webfont.modules.google.FontApiUrlBuilder');
goog.require('webfont.modules.google.FontApiParser');
goog.require('webfont.FontWatchRunner');

/**
 * @constructor
 * @implements {webfont.FontModule}
 */
webfont.modules.google.GoogleFontApi = function(userAgent, domHelper, configuration) {
  this.userAgent_ = userAgent;
  this.domHelper_ = domHelper;
  this.configuration_ = configuration;
};

webfont.modules.google.GoogleFontApi.NAME = 'google';

goog.scope(function () {
  var GoogleFontApi = webfont.modules.google.GoogleFontApi,
      FontWatchRunner = webfont.FontWatchRunner,
      FontApiUrlBuilder = webfont.modules.google.FontApiUrlBuilder,
      FontApiParser = webfont.modules.google.FontApiParser;

  GoogleFontApi.METRICS_COMPATIBLE_FONTS = {
    "Arimo": true,
    "Cousine": true,
    "Tinos": true
  };

  GoogleFontApi.prototype.supportUserAgent = function(userAgent, support) {
    support(userAgent.getBrowserInfo().hasWebFontSupport());
  };

  GoogleFontApi.prototype.load = function(onReady) {
    var domHelper = this.domHelper_;
    var nonBlockingIe = this.userAgent_.getName() == 'MSIE' &&
        this.configuration_['blocking'] != true;

    if (nonBlockingIe) {
      domHelper.whenBodyExists(goog.bind(this.insertLink_, this, onReady));
    } else {
      this.insertLink_(onReady);
    }
  };

  GoogleFontApi.prototype.insertLink_ = function(onReady) {
    var domHelper = this.domHelper_;
    var fontApiUrlBuilder = new FontApiUrlBuilder(
        this.configuration_['api'], domHelper.getProtocol(), this.configuration_['text']);
    var fontFamilies = this.configuration_['families'];
    fontApiUrlBuilder.setFontFamilies(fontFamilies);

    var fontApiParser = new FontApiParser(fontFamilies);
    fontApiParser.parse();

    domHelper.insertInto('head', domHelper.createCssLink(
        fontApiUrlBuilder.build()));
    onReady(fontApiParser.getFonts(), fontApiParser.getFontTestStrings(), GoogleFontApi.METRICS_COMPATIBLE_FONTS);
  };
});

globalNamespaceObject.addModule(webfont.modules.google.GoogleFontApi.NAME, function(configuration, domHelper) {
  var userAgentParser = new webfont.UserAgentParser(navigator.userAgent, document);
  var userAgent = userAgentParser.parse();
  return new webfont.modules.google.GoogleFontApi(userAgent, domHelper, configuration);
});

goog.provide('webfont.modules.Fontdeck');

goog.require('webfont.Font');

/**
 * @constructor
 * @implements {webfont.FontModule}
 */
webfont.modules.Fontdeck = function(domHelper, configuration) {
  this.domHelper_ = domHelper;
  this.configuration_ = configuration;
  this.fonts_ = [];
};

webfont.modules.Fontdeck.NAME = 'fontdeck';
webfont.modules.Fontdeck.HOOK = '__webfontfontdeckmodule__';
webfont.modules.Fontdeck.API = '//f.fontdeck.com/s/css/js/';

goog.scope(function () {
  var Fontdeck = webfont.modules.Fontdeck,
      Font = webfont.Font,
      FontVariationDescription = webfont.FontVariationDescription;

  Fontdeck.prototype.getScriptSrc = function(projectId) {
    var protocol = this.domHelper_.getProtocol();
    // For empty iframes, fall back to main window's hostname.
    var hostname = this.domHelper_.getHostName();
    var api = this.configuration_['api'] || webfont.modules.Fontdeck.API;
    return protocol + api + hostname + '/' + projectId + '.js';
  };

  Fontdeck.prototype.supportUserAgent = function(userAgent, support) {
    var projectId = this.configuration_['id'];
    var loadWindow = this.domHelper_.getLoadWindow();
    var self = this;

    if (projectId) {
      // Provide data to Fontdeck for processing.
      if (!loadWindow[webfont.modules.Fontdeck.HOOK]) {
        loadWindow[webfont.modules.Fontdeck.HOOK] = {};
      }

      // Fontdeck will call this function to indicate support status
      // and what fonts are provided.
      loadWindow[webfont.modules.Fontdeck.HOOK][projectId] = function(fontdeckSupports, data) {
        for (var i = 0, j = data['fonts'].length; i<j; ++i) {
          var font = data['fonts'][i];
          self.fonts_.push(new Font(font['name'], Font.parseCssVariation('font-weight:' + font['weight'] + ';font-style:' + font['style'])));
        }
        support(fontdeckSupports);
      };

      // Call the Fontdeck API.
      var script = this.domHelper_.createScriptSrc(this.getScriptSrc(projectId));
      this.domHelper_.insertInto('head', script);

    } else {
      support(true);
    }
  };

  Fontdeck.prototype.load = function(onReady) {
    onReady(this.fonts_);
  };
});

globalNamespaceObject.addModule(webfont.modules.Fontdeck.NAME, function(configuration, domHelper) {
  return new webfont.modules.Fontdeck(domHelper, configuration);
});

if (window['WebFontConfig']) {
  globalNamespaceObject['load'](window['WebFontConfig']);
}

