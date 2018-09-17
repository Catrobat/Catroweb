//
// This file contains the classes for the
//  * Event tracking and
//  * YouTube video tracking
//



if ((window.doNotTrack || navigator.doNotTrack || navigator.msDoNotTrack || 'msTrackingProtectionEnabled' in window.external) &&
  (window.doNotTrack === '1' || navigator.doNotTrack === 'yes' || navigator.doNotTrack === '1' || navigator.msDoNotTrack === '1'))
{
  // The browser supports Do Not Track!
  // Do Not Track is enabled!
}
else
{
  
  // Do Not Track is not supported || // Do Not Track is disabled!
  
  (function ($, window, document, undefined) {
    
    'use strict';
    
    /**
     * Contains Helper functions
     *
     * @module Tracker
     * @class HelperFunctions
     * @memberof Tracker
     * @static
     * @final
     * @readonly
     */
    let HelperFunctions = {
      
      /*
      * Returns the hostname of the currently viewed window
      *
      * @method getHostname
      * @return {String} Hostname
      */
      getHostname: function () {
        return window.location.hostname;
      },
      
      /*
      * Returns the value of the URL after program/
      *
      * @method getProgramNumberFromUrl
      * @return {String} value
      */
      getProgramNumberFromUrl: function () {
        
        if (window.location === undefined) return undefined;
        
        if (/.*program\/\d+$/.test(window.location.pathname))
        {
          return HelperFunctions.getLastValue(window.location.pathname);
        }
        else return undefined;
      },
      
      /*
      * Returns the value of the last connected digits
      *
      * @method getLastValue
      * @param {string} uri - string to extract the last value
      * @return {string} value
      */
      getLastValue: function (uri) {
        
        if (uri === undefined || typeof(uri) !== "string") return undefined;
        
        return uri.match(/\d+$/)[0];
      },
      
      /*
       * Function to remove query parameter and the current domain from the uri
       * Format {domain}/page/subpage?{parameter}
       * @method removeDomainAndQuery
       * @param {String} uri - URI
       * @return {String} uri
      */
      removeDomainAndQuery: function (uri) {
        
        if (uri === undefined || typeof(uri) !== "string") return undefined;
        
        let hostname = HelperFunctions.getHostname();
        
        // remove protocol = "https:"
        if (uri.indexOf(window.location.protocol) >= 0)
        {
          uri = uri.replace(window.location.protocol + "//", "");
        }
        
        // remove hostdomain
        if (uri.indexOf(hostname) >= 0)
        {
          uri = uri.replace(hostname, "");
        }
        
        // remove query parameters
        uri = HelperFunctions.removeQueryParameterFromUri(uri);
        
        return uri;
      },
      
      /*
       * Function to remove query parameter from the uri
       * Format /page/subpage?{parameter}
       * @method removeQueryParameterFromUri
       * @param {String} uri - URI
       * @return {String} uri
      */
      removeQueryParameterFromUri: function (uri) {
        
        if (uri === undefined || typeof(uri) !== "string") return undefined;
        
        // remove query parameters
        if (uri.indexOf('?') >= 0)
        {
          uri = uri.substring(0, uri.indexOf('?'));
        }
        
        return uri;
      },
      
      /*
       * Checks if the input string is null or whitespace
       * @method isNullOrWhitespace
       * param{string} text - input parameter
      */
      isNullOrWhitespace: function (text) {
        if (text === undefined || typeof(text) !== "string") return undefined;
        
        return (!text || text.trim() === "");
      },
      
      /* Returns the the query parameter from the given url
       * @method getParameterByName
       * @param{String} name - the query parameter
       * @param{String} url - the url
       * @return{String} - the query value
      */
      getParameterByName: function (name, url) {
        
        if (name === undefined || typeof(name) !== "string") return "";
        if (url === undefined || typeof(url) !== "string") return "";
        
        let match = RegExp('[?&]' + name + '=([^&]*)').exec(url);
        return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
      },
      
      /* Returns an array of 3 elements for the tracking of
       * a link
       * a more button
       * a less button
       * @method createTripleElementProgram
       * @param{String} ident - the HTML ID
       * @param{String} descriptor - the name of the action
       * @param{String} category - the category
       * @return{Array} - an array of three elements
      */
      createTripleElementProgram: function (ident, descriptor, category) {
        
        let tripleList = [
          {
            /*
             * Tracks the clicks on the programs
            */
            'BaseSelector': '#' + ident,
            'SubSelector' : 'a',
            'Category'    : category,
            'Action'      : 'click - ' + descriptor + ' programs',
            'Label'       : function (e) { return HelperFunctions.removeDomainAndQuery($(e).attr('href')); },
          },
          {
            /*
             * Tracks the clicks on the more button
            */
            'BaseSelector': '#' + ident,
            'SubSelector' : '.button-show-more',
            'Category'    : category,
            'Action'      : 'click - ' + descriptor + ' programs - more',
          },
          {
            /*
             * Tracks the clicks on the less button
             */
            'BaseSelector': '#' + ident,
            'SubSelector' : '.button-show-less',
            'Category'    : category,
            'Action'      : 'click - ' + descriptor + ' programs - less',
          },
        ];
        
        return tripleList;
      }
      
    };
    
    // Register the HelperFunctions in the global window context
    window.HelperFunctions = HelperFunctions;
    
  })(window.jQuery, window, document);
  
  (function ($, window, document, undefined) {
    
    'use strict';
    
    /**
     * UrlMapper
     * maps the url defined in the map
     *
     * @module Tracker
     * @class UrlMapper
     * @memberof Tracker
     * @requires jQuery
     * @static
     */
    let UrlMapper = {
      /*
       * Function to remove query parameter from the uri
       * Format /page/subpage?{parameter}
       * @method removeQueryParameterFromUri
       * @param {String} uri - URI
       * @return {String} uri
      */
      map: function (url, mappings) {
        
        url = url || window.location.href || '';
        mappings = mappings || [];
        
        $.each(mappings, function (j, map) {
          if (map.RegEx.test(url))
          {
            url = url.match(map.RegEx)[map.ValueIndex];
            return url;
          }
        });
        
        return url;
      },
    };
    
    // Register the UrlMapper in the global window context
    window.UrlMapper = UrlMapper;
    
  })(window.jQuery, window, document);
  
  (function ($, window, document, undefined) {
    
    'use strict';
    
    /**
     * TrackingObjectFactory
     * Factory to create Tracking Objects
     *
     * @module Tracker
     * @class TrackingObjectFactory
     * @memberof Tracker
     * @requires jQuery
     * @static
     */
    let TrackingObjectFactory = {
      /*
       * Function to create trackingObjects from plain object
       * @method trackingObjectFactoryArray
       * @param {Array[Object]} trackingObjectArray - Source Array for the trackingObjects
       * @return {Array[TrackingObject]} - Array of trackingObjects
  */
      createArray: function (trackingObjectArray) {
        
        let returnArray = [];
        
        jQuery.each(trackingObjectArray, function (index, elementToTrack) {
          returnArray.push(TrackingObjectFactory.createObj(elementToTrack));
        });
        
        return returnArray;
      },
      
      /*
       * Function to create a trackingObject from plain object
       * @method trackingObjectFactoryObj
       * @param {Object} obj - Source for the trackingObject
       * @return {TrackingObject} - TrackingObjects
       */
      createObj: function (obj) {
        
        let trackingObject = new TrackingObject();
        
        Object.keys(obj).forEach(function (key, index) {
          // key: the name of the object key
          // index: the ordinal position of the key within the object
          switch (key)
          {
            case 'BaseSelector':
              trackingObject.addBaseSelector(obj[key]);
              break;
            case 'SubSelector':
              trackingObject.addSubSelector(obj[key]);
              break;
            default:
              trackingObject.addEventParameter(key, obj[key]);
          }
          ;
        });
        
        if (AnalyticsTracker.debugOutput)
        {
          AnalyticsTracker.isBaseSeletorValid(trackingObject.baseSelector);
        }
        
        return trackingObject;
      },
    };
    
    // Register the TrackingObjectFactory in the global window context
    window.TrackingObjectFactory = TrackingObjectFactory;
    
  })(window.jQuery, window, document);
  
  (function ($, window, document, undefined) {
    
    'use strict';
    
    let TrackingObject;
    
    TrackingObject = (function () {
      /**
       * Tracking Object
       * contrains the selectors and the event parameter for the analytics tracking
       *
       * @module Tracker
       * @class TrackingObject
       * @memberof Tracker
       * @constructor
       */
      TrackingObject = function () {
        /**
         * The Event parameter for the event tracking
         * @property Parameter
         * @type collection
         */
        this.baseSelector = "";
        
        /**
         * The Event parameter for the event tracking
         * @property Parameter
         * @type collection
         */
        this.subSelector = "";
        
        /**
         * The Event parameter for the event tracking
         * @property Parameter
         * @type collection
         */
        this.eventParameter = {};
      };
      
      /**
       * Adds the base identifier for the tracked element
       *
       * @method addBaseSelector
       * @param {String} baseSelector - The indentifier of the base element to be tracked
       * @return {TrackingObject} - returns same object
       */
      TrackingObject.prototype.addBaseSelector = function (baseSelector) {
        this.baseSelector = baseSelector;
        return this;
      };
      
      /**
       * Adds a sub identifier for the tracked element
       *
       * @method addSubSelector
       * @param {String} subSelector - The indentifier of the sub element to be tracked
       * @return {TrackingObject} - returns same object
       */
      TrackingObject.prototype.addSubSelector = function (subSelector) {
        this.subSelector = subSelector;
        return this;
      };
      
      /**
       * Adds an event parameter
       *
       * @method addEventParameter
       * @param {String} key - The name of the event parameter
       * @param {String} value - The value of the event parameter
       * @return {TrackingObject} - returns same object
       */
      TrackingObject.prototype.addEventParameter = function (key, value) {
        this.eventParameter[key] = value;
        return this;
      };
      
      /**
       * Returns the event parameters
       *
       * @method getEventParameter
       * @return {Object} - returns the event parameter
       */
      TrackingObject.prototype.getEventParameter = function () {
        return this.eventParameter;
      };
      
      /**
       * Returns true if a Subselector exits
       *
       * @method hasSubselector
       * @return {boolean} - true if a subselector exits, else false
       */
      TrackingObject.prototype.hasSubSelector = function () {
        return ((this.subSelector.length > 0) ? true : false);
      };
      
      /**
       * Creates a deep copy of the TrackingObject
       *
       * @method copy
       * @return {TrackingObject} - returns copy of the object
       */
      TrackingObject.prototype.copy = function () {
        
        let copy = new TrackingObject();
        let toCopy = this;
        
        // iterates the parameters of the TrackingObject and
        // executes functions of the parametes
        Object.keys(toCopy).forEach(function (key, index) {
          // key: the name of the object key
          // index: the ordinal position of the key within the object
          copy[key] = ((typeof(copy[key]) === "object") ? Object.assign({}, toCopy[key]) : toCopy[key]);
        });
        return copy;
      };
      
      return TrackingObject;
      
    })();
    
    // Register the TrackingObject in the global window context
    window.TrackingObject = TrackingObject;
    
  })(window.jQuery, window, document);
  
  (function ($, window, document, undefined) {
    
    'use strict';
    
    /**
     * Contains methods to create tracking objects and
     * add click event handlers to html elements
     *
     * @module Tracker
     * @class AnalyticsTracker
     * @memberof Tracker
     * @requires jQuery
     * @static
     */
    let AnalyticsTracker;
    
    AnalyticsTracker = {
      
      /**
       * Indicates if the send event should be displayed
       * @property debugOutput
       * @type boolean
       */
      debugOutput: false,
      
      /**
       * Indicates if the event is send
       * @property isSending
       * @type boolean
       */
      isSending: true,
      
      /**
       * Contains the tracking function of the analytics system
       * @property trackingFunction
       * @type function
       */
      trackingFunction: undefined,
      
      /**
       * Contains the filter for the valid download extensions
       * @property validDownloadExtensions
       * @type string
       */
      validDownloadExtensions: [],
      
      /**
       * Contains the mapping of the tracking actions
       * @property trackingVarMap
       * @type Array
       */
      trackingVarMap: [],
      
      /**
       * initializes the AnalyticsTracker withe the tracking function
       * @method initialize
       * @param {function} trackingFunction - the tracking function
       * @param {array} map - the mapping of the tracking actions
       * @param {array} validDownloadExtensions - valid download extions for the event tracking
       */
      initialize: function (trackingFunction, map, validDownloadExtensions) {
        AnalyticsTracker.trackingFunction = trackingFunction;
        AnalyticsTracker.trackingVarMap = map;
        AnalyticsTracker.validDownloadExtensions = validDownloadExtensions;
      },
      
      /**
       * adds the data.eventParameter to the object
       * @method addEventParameter
       * @param {object} obj - the object
       * @param {object} eventParameter - event parameter to add
       */
      addEventParameter: function (obj, eventParameter) {
        obj.data = obj.data || {};
        obj.data.eventParameter = eventParameter;
      },
      
      /*
       * Function to track all email links on the web page
       * @method trackEmails
       * @param {object} parameter - contains the parameter for the tracking
       */
      trackEmails: function (parameter) {
        parameter.BaseSelector = 'a[href^="mailto"]';
        let trackingObject = TrackingObjectFactory.createObj(parameter);
        
        AnalyticsTracker.trackOnClickEvent(trackingObject);
      },
      
      /*
       * Function to track the field by keypress enter
       * @method trackOnKeyPressEnter
       * @param {string} parameter - parameter for tracking
       */
      trackOnKeyPressEnter: function (parameter) {
        let trackingObject = TrackingObjectFactory.createObj(parameter);
        
        // search for the searchbox
        jQuery(trackingObject.baseSelector).keypress(function (e) {
          // Keypress 13 = Enter
          if (e.which == 13)
          {
            AnalyticsTracker.addEventParameter(e, trackingObject.getEventParameter());
            AnalyticsTracker.resolve(e);
          }
        });
      },
      
      /*
       * Function to interpret the trackingobject parameters and send the Google Analytics event
       * @method resolve
       * @param {event} event - click event send from jQuery
       */
      resolve: function (event) {
        // gets the event parameters
        let params = event.data.eventParameter;
        
        // iterates the parameters of the TrackingObject and
        // executes functions of the parametes
        Object.keys(params).forEach(function (key, index) {
          // key: the name of the object key
          // index: the ordinal position of the key within the object
          if (typeof(params[key]) === 'function' && key.indexOf("callback") < 0)
          {
            params[key] = params[key](event.currentTarget);
          }
        });
        
        AnalyticsTracker.sendEvent(event.currentTarget, params);
      },
      
      /*
       * Function to map the event parameters accordingly to the tracking let Map
       * @method mapEventParameter
       * @param {object} actions - parameters to map
       * @return {object} - mapped parameters
       */
      mapEventParameter: function (actions) {
        
        let mapped = {};
        
        Object.keys(actions).forEach(function (key, index) {
          
          let mapIndex = AnalyticsTracker.trackingVarMap.findIndex(function (x) { return key in x });
          if (mapIndex >= 0)
          {
            
            let parameter = AnalyticsTracker.trackingVarMap[mapIndex];
            let mappedKey = parameter[key];
            
            if (typeof(actions[key]) === "string" && HelperFunctions.isNullOrWhitespace(actions[key]) === false ||
              typeof(actions[key]) === "number")
            {
              
              mapped[mappedKey] = actions[key];
            }
          }
          else
          {
            mapped[key] = actions[key];
          }
        });
        
        return mapped;
      },
      
      /*
       * Function to send tracking event to the analytics system
       * @method sendEvent
       * @param {HTMLElement} element - element which triggered the click
       * @param {Object} eventParameter  - event Parameter to send
      */
      sendEvent: function (element, eventParameter) {
        
        let mapped = AnalyticsTracker.mapEventParameter(eventParameter);
        
        // prints the tracking parameter
        if (AnalyticsTracker.debugOutput === true)
        {
          console.log("event send : \r\n" + AnalyticsTracker.printObjectParameter(mapped));
        }
        
        if (AnalyticsTracker.isSending === true)
        {
          // execute tracking code
          AnalyticsTracker.trackingFunction('event', eventParameter.Action, mapped);
        }
      },
      
      /*
       * Function to add the click event handler to the elements to track
       * @method trackOnClickEvent
       * @param {TrackingObject} element - Element to track
      */
      trackOnClickEvent: function (trackingObject) {
        
        if (trackingObject.hasSubSelector())
        {
          jQuery(trackingObject.baseSelector).on('click', trackingObject.subSelector, function (e) {
            
            let tracking_copy = trackingObject.copy();
            AnalyticsTracker.addEventParameter(e, tracking_copy.getEventParameter());
            AnalyticsTracker.resolve(e);
          });
        }
        else
        {
          jQuery(trackingObject.baseSelector).on('click', function (e) {
            
            let tracking_copy = trackingObject.copy();
            AnalyticsTracker.addEventParameter(e, tracking_copy.getEventParameter());
            AnalyticsTracker.resolve(e);
          });
        }
      },
      
      /*
       * Function to register the TrackingObjects for Click tracking
       * @method registerElementsForClickTracking
       * @param  {Array[TrackingObject]} trackingList - Array of trackingObjects
       */
      registerElementsForClickTracking: function (trackingList) {
        
        if (Array.isArray(trackingList))
        {
          jQuery.each(trackingList, function (index, trackingObjectToTrack) {
            AnalyticsTracker.trackOnClickEvent(trackingObjectToTrack);
          });
        }
        else
        {
          AnalyticsTracker.trackOnClickEvent(trackingList);
        }
      },
      
      /*
       * Function to add click event handler to all outbound links and
       * to all internal downloads
       * @method trackOutboundAndDownloads
       * @param {Object} parameter - contains the extensions for the tracked internal downloads
       */
      trackOutboundAndDownloads: function (parameter) {
        // select all a with are not beginning with '#'
        jQuery("a:not([href^='#'])").filter(function () {
          // filter only links beginning with http
          return /^http.*/.test(this.href);
        }).each(function () {
          // Add click event to the link to track the outbound link
          let _hostDomain = HelperFunctions.getHostname();
          if (this.href.indexOf(_hostDomain) < 0)
          {
            // is external link
            jQuery(this).on("click", function (sender) {
              
              if (this.href !== undefined)
              {
                let url = this.href;
                let trackingObject = TrackingObjectFactory.createObj(parameter.Outbound);
                
                if (jQuery(this).attr("target") !== "_blank")
                {
                  trackingObject = trackingObject.addEventParameter("event_callback", function () { document.location = url;});
                }
                
                AnalyticsTracker.addEventParameter(sender, trackingObject.getEventParameter());
                AnalyticsTracker.resolve(sender);
              }
            });
          }
          else
          {
            // is internal downloads
            jQuery(this).filter(function () {
              // file extensions
              // valid download extensions
              return AnalyticsTracker.validDownloadExtensions.test(this.href);
            }).each(function () {
              jQuery(this).on("click", function (sender) {
                
                let trackingObject = TrackingObjectFactory.createObj(parameter.Downloads);
                AnalyticsTracker.addEventParameter(sender, trackingObject.getEventParameter());
                AnalyticsTracker.resolve(sender);
              });
            });
          }
        });
      },
      
      /*
       * Function to check if the base selector is valid
       * on the current page
       * @method isBaseSeletorValid
       * @param {string} baseSelector - the base selector
       * @return {boolean}
       */
      isBaseSeletorValid: function (baseSelector) {
        let isValid = false;
        // Chech if the tracked object exits on the page
        if ($(baseSelector).length)
        {
          console.log("Selector found: " + baseSelector);
          isValid = true;
        }
        else
        {
          console.log("%cSelector not found: " + baseSelector, "color:red");
        }
        
        return isValid;
      },
      
      /*
       * Function to print the parameter of an objects as string
       * @method printObjectParameter
       * @param {object} obj - the object
       * @return {string}
      */
      printObjectParameter: function (obj) {
        
        let output = "";
        Object.keys(obj).forEach(function (key, index) {
          // key: the name of the object key
          // index: the ordinal position of the key within the object
          output += key + ": " + obj[key] + "\r\n";
        });
        
        return output;
      },
    };
    
    // Register the AnalyticsTracker in the global window context
    window.AnalyticsTracker = AnalyticsTracker;
    
  })(window.jQuery, window, document);
  
  (function ($, window, document, undefined) {
    /**
     * YoutubeTracker
     * contains function to track Youtoube videos
     *
     * @module Tracker
     * @class YoutubeTracker
     * @memberof Tracker
     * @requires jQuery
     * @static
     */
    let YoutubeTracker;
    
    YoutubeTracker = {
      
      /**
       * Contains the reference to the AnalyticsTracker
       * @property analyticsTracker
       * @type AnalyticsTracker
       */
      analyticsTracker: undefined,
      
      /**
       * Defines the percentage of the video video content in which tracking events are send
       * @property percentageToTrack
       * @type Array[interger]
       */
      percentageToTrack: [25, 50, 75],
      
      /**
       * Contains all Youtoube Players
       * @property playerArray
       * @type Array[YT.Player]
       */
      playerArray: [],
      
      /**
       * Internal array to store currently playing videos
       * @property players
       * @type Array[YT.Player]
       */
      players: [],
      
      /**
       * Contains handle of the intervall thread
       * @property intervalId
       * @type integer
       */
      intervalId: 0,
      
      /*
       * Function initializes the YoutubeTracker
       * to all internal downloads
       * @method initialize
       * @param {AnalyticsTracker} analyticsTracker - contains a reference to the AnalyticsTracker
       */
      initialize: function (analyticsTracker) {
        YoutubeTracker.analyticsTracker = analyticsTracker;
      },
      
      /*
       * Function to get the percentage viewed for each video
       * creates new if not exists
       * @method getPercentage
       * @param {string} id - ID if the video
       * @return {integer} tracked percent of the video
       */
      getPercentage: function (id) {
        if (YoutubeTracker.players[id] === undefined)
        {
          YoutubeTracker.players[id] = {};
        }
        
        if (YoutubeTracker.players[id].timePercent === undefined)
        {
          YoutubeTracker.players[id].timePercent = 0;
        }
        
        return YoutubeTracker.players[id].timePercent;
      },
      
      /*
       * Function to set the percentage tracked for each video
       * creates new if not exists
       * @method setPercentage
       * @param {string} id - ID if the video
       * @param {integer} value - percentage viewed
       */
      setPercentage: function (id, value) {
        if (YoutubeTracker.players[id] === undefined)
        {
          YoutubeTracker.players[id] = {};
        }
        
        YoutubeTracker.players[id].timePercent = value;
      },
      
      /*
       * Get the id of the Youtube video
       * @method getId
       * @param {event} event - youtube video event
       * @return {string} id of the video
      */
      getId: function (event) {
        return event.getVideoData().video_id;
      },
      
      /*
       * Registers the YoutubeVideos on the web page for the viewing
       * @method registerYoutubeVideos
      */
      registerYoutubeVideos: function () {
        
        let regex = /(http)?s?\:?\/\/www\.youtube\.com\/embed\/([\w-]{11})(?:\?.*)?/;
        let i = 0;
        
        $('iframe').each(function (i, element) {
          if (regex.test(element.src))
          {
            $(this).load(function () {
              let matches = element.src.match(regex);
              $(element).attr('data-video-id', matches[2]);
              YoutubeTracker.onYouTubeIframeAPIReady(element, matches[2]);
            });
          }
          else
          { return false; }
        });
      },
      
      /*
       * Method to track changes in the Youtube video player
       * @method checkStateChange
       * @param {event} event - Youtube video event
      */
      checkStateChange: function (event) {
        
        let player = event.target;
        let id = YoutubeTracker.getId(player);
        
        player.PlayerState = event.data;
        
        if (event.data == YT.PlayerState.UNSTARTED)
        {
          player.lastPlayerState = YT.PlayerState.UNSTARTED;
        }
        // track when user clicks Play
        if (event.data == YT.PlayerState.PLAYING && player.lastPlayerState != YT.PlayerState.PLAYING)
        {
          player.lastPlayerState = YT.PlayerState.PLAYING;
          YoutubeTracker.trackEvent(player, id, 'play', '');
          YoutubeTracker.startChecking(player);
          
        }
        // track when user clicks Pause
        if (event.data == YT.PlayerState.PAUSED)
        {
          player.lastPlayerState = YT.PlayerState.PAUSED;
          YoutubeTracker.trackEvent(player, id, 'pause', '');
        }
        // track when video ends
        if (event.data == YT.PlayerState.ENDED)
        {
          player.lastPlayerState = YT.PlayerState.ENDED;
          YoutubeTracker.setPercentage(id, 0);
          YoutubeTracker.trackEvent(player, id, 'ended', '');
        }
        // track buffering
        if (event.data == YT.PlayerState.BUFFERING)
        {
          player.lastPlayerState = YT.PlayerState.BUFFERING;
          YoutubeTracker.trackEvent(player, id, 'buffering', '');
        }
      },
      
      /*
       * Function to start the tracking thread
       * for the tracking of the video players
       * @method startChecking
       * @param {YoutubePlayer} player - Youtube video player
      */
      startChecking: function (player) {
        
        // check if not already started
        if (YoutubeTracker.intervalId === 0)
        {
          YoutubeTracker.intervalId = setInterval(YoutubeTracker.checkPercentage, 500);
        }
        
        let id = YoutubeTracker.getId(player);
        let isAdded = false;
        
        Object.keys(YoutubeTracker.players).forEach(function (key, index) {
          if (key == id)
          {
            isAdded = true;
          }
        });
        
        if (isAdded === false)
        {
          YoutubeTracker.players[id] = player;
        }
      },
      
      /*
       * Function to stop the interval, if not already stopped
       * @method stopChecking
       */
      stopChecking: function () {
        // check if not already stopped
        if (YoutubeTracker.intervalId !== 0)
        {
          clearInterval(YoutubeTracker.intervalId);
          YoutubeTracker.intervalId = 0;
        }
      },
      
      /*
       * Interall Function to check periodicaly the states of the plying youtube videos
       * and sends a tracking event if a tracked percentage is reached
       * @method checkPercentage
       */
      checkPercentage: function () {
        
        let anyPlaying = false;
        
        Object.keys(YoutubeTracker.players).forEach(function (key, index) {
          
          let player = YoutubeTracker.players[key];
          
          if (player.PlayerState == YT.PlayerState.PLAYING)
          {
            
            anyPlaying = true;
            let trackedPercentage = YoutubeTracker.getPercentage(key);
            
            let duration = player.getDuration();
            let currentTime = player.getCurrentTime();
            
            if (duration > 0)
            {
              let currentPerc = (currentTime / duration) * 100;
              currentPerc = Math.round(currentPerc);
              
              // check each percentage
              $.each(YoutubeTracker.percentageToTrack, function (j, percentage) {
                // check if we reached a defined percentage
                if (trackedPercentage < percentage && currentPerc > percentage)
                {
                  YoutubeTracker.setPercentage(key, percentage);
                  YoutubeTracker.trackEvent(player, key, "watched-" + percentage + "%", '');
                }
              });
            }
          }
        });
        
        // if no video is currently playing stop the intervall
        if (!anyPlaying)
        {
          YoutubeTracker.stopChecking();
        }
      },
      
      /*
       * Function to create YT.Palyers object and and adds the to the monitored videos
       * add EventHanlder to the onReady and onStateChange events
       * @method onYouTubeIframeAPIReady
       * @param {Youtube video element} element - Youtube video element
       * @param {string} id - id of the video
       */
      onYouTubeIframeAPIReady: function (element, id) {
        if (YoutubeTracker.playerArray[id] === undefined)
        {
          YoutubeTracker.playerArray[id] = new YT.Player(element, {
            events: {
              'onReady'      : YoutubeTracker.onPlayerReady,
              'onStateChange': YoutubeTracker.onPlayerStateChange
            }
          });
        }
      },
      
      /*
       * Event handler function for the onReady event of the Youtube player
       * @method onPlayerReady
       * @param {Youtube video element} event - Youtube video event
       */
      onPlayerReady: function (event) { },
      
      /*
       * Event handler function for the onStateChange event of the Youtube player
       * @method onPlayerStateChange
       * @param {Youtube video element} event - Youtube video event
       */
      onPlayerStateChange: function (event) {
        YoutubeTracker.checkStateChange(event);
      },
      
      /*
       * Function to enable the Youtoube tracking api
       * adds the parameter
       * * enablejsapi = 1
       * * origin = current website domain
       * to the uri of the video
       * and loads the tracking api
       * @method addTrackingApiToYoutoubeVideos
       */
      addTrackingApiToYoutoubeVideos: function () {
        
        // Enable JSAPI if it's not already on the URL
        $('iframe').each(function (i, elem) {
          if (/youtube.com\/embed/.test(elem.src))
          {
            if (elem.src.indexOf('enablejsapi=') === -1)
            {
              elem.src += (elem.src.indexOf('?') === -1 ? '?' : '&') + 'enablejsapi=1&origin=' + window.location.origin;
            }
          }
        });
        
        //load the tracking script
        $.getScript("https://www.youtube.com/iframe_api");
      },
      
      /*
       * Sends the event to the Analytics tracker
       * @method trackEvent
       * @param {event} event - Youtube video event
       * @param {string} id - id of the youtube video
       * @param {string} action - action of the event
       * @param {string} label - label of the event
       */
      trackEvent: function (event, id, action, label) {
        
        let videoName = event.getVideoData().title;
        let actionName = action + ' - ' + videoName;
        
        let trackingObject = TrackingObjectFactory.createObj({
          'Category': "videos",
          'Action'  : actionName,
          'Label'   : window.location.pathname,
        });
        
        YoutubeTracker.sendEvent(trackingObject.getEventParameter());
      },
      
      /*
       * Function to send the analytics event
       * @method sendEvent
       * @param {Object} parameter - event parameter of the event
      */
      sendEvent: function (parameter) {
        
        if (YoutubeTracker.analyticsTracker === undefined)
        {
          console.log("AnalyticsTracker is undefined");
          return;
        }
        YoutubeTracker.analyticsTracker.sendEvent(null, parameter);
      },
    };
    
    // Register the YoutubeTracker in the global window context
    window.YoutubeTracker = YoutubeTracker;
    
  })(window.jQuery, window, document);
  
  (function ($, window, document, configCustom, undefined) {
    
    'use strict';
    
    // initializes the configCustom as copy of configCustom or as empty object
    configCustom = configCustom || {};
    
    /**
     * configuration object to set global configuration parameters
     * @param {String} UaId - Google Analytics tracking id
     */
    let configDefault = {
      'UaId'                   : 'UA-42270417-5',
      'validDownloadExtensions': /\.*.(zip|rar|mp\\d+|mpe*g|pdf|docx*|pptx*|xlsx*|jpe*g|png|gif|tiff*|avi|svg)/,
      'GoogleAnalyticsUri'     : 'https://www.googletagmanager.com/gtag/js?id=',
      'DebugOutput'            : false,
      
      'UrlMapping': [
        {
          // removes the parameters from the program url
          // origin:  https://share.catrob.at/pocketcode/program/44132
          // cleaned: https://share.catrob.at/pocketcode/program/
          'RegEx'     : /(.*\/program\/)(\d+)/,
          'ValueIndex': 1,
        },
        {
          // removes the parameters from the search and tag search url
          // origin:  http://127.0.0.1/pocketcode/search/test
          // cleaned: http://127.0.0.1/pocketcode/search/
          // or
          // https://share.catrob.at/pocketcode/search/test%20der%20test
          // https://share.catrob.at/pocketcode/search/
          // or
          // https://share.catrob.at/pocketcode/tag/search/1
          // https://share.catrob.at/pocketcode/tag/search/
          'RegEx'     : /(.*\/search\/)(.*)/,
          'ValueIndex': 1,
        },
        {
          // removes the query parameters from every url
          // origin:  /pocketcode/?username=sonicjack2007&token=b1889b23f0f77ece495f5b2f9b358289
          // cleaned: /pocketcode/
          'RegEx'     : /(.*\/?)(\?.*)/,
          'ValueIndex': 1,
        }
      ],
      
      // Mapping from the tracking object parameter to the analytics parameters
      // Parameters required are:
      // * Category
      // * Action
      // * Label
      // * Value
      'TrackingVarMap': [
        {'Category': 'event_category'},
        {'Action': 'event_action'},
        {'Label': 'event_label'},
        {'Value': 'value'},
      ],
    };
    
    // copy and overwrite the custom parameter to the default parameters
    Object.keys(configCustom).forEach(function (key, index) {
      configDefault[key] = configCustom[key];
    });
    
    // defintion of the Google Analytics tracking function
    function gtag () { dataLayer.push(arguments); }
    
    // configurate the Analytics Tracker
    AnalyticsTracker.initialize(gtag, configDefault.TrackingVarMap, configDefault.validDownloadExtensions);
    AnalyticsTracker.debugOutput = configDefault.DebugOutput;
    // configurate the Youtube Tracker
    YoutubeTracker.initialize(AnalyticsTracker);
    
    // loads the analytics script and tracks the pageview
    $.getScript(configDefault.GoogleAnalyticsUri + configDefault.UaId, function () {
      
      if (AnalyticsTracker.isSending === true)
      {
        
        gtag('js', new Date());
        // map the url
        let href = UrlMapper.map(window.location.href, configDefault.UrlMapping);
        gtag('config', configDefault.UaId, {
          'anonymize_ip' : true,
          'page_location': href,
        });
      }
    });
    
    /**
     * Document realdy function
     * executes when the DOM of the document is ready for manipulation
     */
    jQuery(document).ready(function () {
      
      // check if the hostname is avialable else abort the script
      if (HelperFunctions.getHostname() === undefined) return;
      
      // Track Youtube Videos
      // registers the Youtube videos on the web page for the tracking
      YoutubeTracker.addTrackingApiToYoutoubeVideos();
      YoutubeTracker.registerYoutubeVideos();
      
      // track emails
      AnalyticsTracker.trackEmails({
        'Category': "engagement",
        'Action'  : "send email",
        'Label'   : function (e) { return e.href.replace(/^mailto\:/i, ''); },
      });
      
      // track the search input
      // the input button
      let searchButton = TrackingObjectFactory.createObj({
        'BaseSelector': 'nav button.btn-search.catro-search-button',
        'Category'    : "engagement",
        'Action'      : "search",
        'Label'       : function (e) { return e.previousElementSibling.value; },
        'search_term' : function (e) { return e.previousElementSibling.value; },
      });
      
      AnalyticsTracker.registerElementsForClickTracking(searchButton);
      
      //
      // tracks the search input
      AnalyticsTracker.trackOnKeyPressEnter({
        'BaseSelector': 'nav input.search-input-header',
        'Category'    : "engagement",
        'Action'      : "search",
        'Label'       : function (e) { return e.value; },
        'search_term' : function (e) { return e.value; },
      });
      
      // tracks the outbound links and downloads
      AnalyticsTracker.trackOutboundAndDownloads({
        'Outbound' : {
          'Category'      : "outbound",
          'Action'        : function (e) { return "outbound - " + e.href.replace(/^https?\:\/\//i, ''); },
          'Label'         : window.location.pathname,
          'transport_type': 'beacon',
          'pathname'      : window.location.pathname,
        },
        'Downloads': {
          'Category' : "download",
          'Action'   : function (e) { return "download - " + e.href; },
          'Label'    : function (e) { return e.value; },
          'file_path': function (e) { return e.href; },
          'pathname' : window.location.pathname,
        },
      });
      
      if (/^(\/[a-zA-Z@]+\/)(?![a-zA-Z]+)(.*)/.test(window.location.pathname))
      {
        
        // sets the category name of the branch
        
        let homepageCategory = 'home page';
        
        let elementList = [
          {
            /**
             * Tracks the clicks on the featured programs
             * featuredPrograms
             */
            'BaseSelector': '#featuredPrograms',
            'SubSelector' : 'a',
            'Category'    : homepageCategory,
            'Action'      : 'click - featured programs',
            'Label'       : function (e) { return HelperFunctions.removeDomainAndQuery($(e).attr('href')); },
          },
          {
            /*
             * Tracks the clicks on the help button
             */
            'BaseSelector': '#recommended .help-icon',
            'Category'    : homepageCategory,
            'Action'      : 'click - recommended programs - help',
          }
        ];
        
        if (/\/(pocketalice|pocketgalaxy)\/.*/.test(window.location.pathname))
        {
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('submissions', 'submissions', homepageCategory));
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('sample', 'sample', homepageCategory));
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('related', 'related', homepageCategory));
        }
        else
        {
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('newest', 'newest', homepageCategory));
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('recommended', 'recommended', homepageCategory));
          elementList.push.apply(elementList,
            HelperFunctions.createTripleElementProgram('mostDownloaded', 'most Downloaded', homepageCategory));
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('mostViewed', 'most viewed', homepageCategory));
          elementList.push.apply(elementList, HelperFunctions.createTripleElementProgram('random', 'random', homepageCategory));
        }
        
        let trackingObjectList = TrackingObjectFactory.createArray(elementList);
        AnalyticsTracker.registerElementsForClickTracking(trackingObjectList);
      }
      
      if (/.*\/login.*/.test(window.location.pathname))
      {
        
        /**
         * Login page tracking code
         */
        let loginCategory = 'engagement';
        
        let elementList = [
          {
            /**
             * Tracks the direct logins
             */
            'BaseSelector': 'button#_submit.login',
            'Category'    : loginCategory,
            'Action'      : 'sign_up',
            'Label'       : 'direct',
            'method'      : 'direct',
          },
          {
            /**
             * Tracks the Google logins
             */
            'BaseSelector': 'a#btn-login_google',
            'Category'    : loginCategory,
            'Action'      : 'sign_up',
            'Label'       : 'Google',
            'method'      : 'Google',
          },
          {
            /**
             * Tracks the facebook logins
             */
            'BaseSelector': 'a#btn-login_facebook',
            'Category'    : loginCategory,
            'Action'      : 'sign_up',
            'Label'       : 'Facebook',
            'method'      : 'Facebook',
          },
        ];
        
        let trackingObjectList = TrackingObjectFactory.createArray(elementList);
        AnalyticsTracker.registerElementsForClickTracking(trackingObjectList);
      }
      
      if (/.*\/program.*/.test(window.location.pathname))
      {
        
        let programCategory = 'program page';
        
        let elementList = [
          {
            /**
             * Tracks the interactive execution of the program
             */
            'BaseSelector': 'button.pc-startButton',
            'Category'    : programCategory,
            'Action'      : 'click - launch program',
            'Label'       : function () {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the clicks on tags
             */
            'BaseSelector': 'div#tag-container',
            'SubSelector' : 'a',
            'Category'    : programCategory,
            'Action'      : function (e) {
              return 'tag - ' + $(e).find('button').attr("id");
            },
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the program download
             */
            'BaseSelector': 'div.download-container > a',
            'Category'    : programCategory,
            'Action'      : 'click - download program',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the apk download
             */
            'BaseSelector': 'div.download-container',
            'SubSelector' : 'button#apk-generate',
            'Category'    : programCategory,
            'Action'      : 'click - download apk',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the apk download
             */
            'BaseSelector': 'div.download-container',
            'SubSelector' : 'button#apk-pending',
            'Category'    : programCategory,
            'Action'      : 'click - download apk pending',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the display of the remix graph
             */
            'BaseSelector': '#remix-graph-button',
            'Category'    : programCategory,
            'Action'      : 'click - show remix graph',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the display of the remix graph by link
             */
            'BaseSelector': '#remix-graph-modal-link',
            'Category'    : programCategory,
            'Action'      : 'click - show remix graph',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the display of code statistics
             */
            'BaseSelector': 'div.show-hide-code-statistic',
            'SubSelector' : 'div.show-hide-code-statistic-arrow:not(.showing-code)',
            'Category'    : programCategory,
            'Action'      : 'click - code statistics - show',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the hide of code statistics
             */
            'BaseSelector': 'div.show-hide-code-statistic',
            'SubSelector' : 'div.show-hide-code-statistic-arrow.showing-code',
            'Category'    : programCategory,
            'Action'      : 'click - code statistics - hide',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the display of program code
             */
            'BaseSelector': 'div.show-hide-code',
            'SubSelector' : 'div.show-hide-code-arrow:not(.showing-code)',
            'Category'    : programCategory,
            'Action'      : 'click - program code - show',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          },
          {
            /**
             * Tracks the hide of program code
             */
            'BaseSelector': 'div.show-hide-code',
            'SubSelector' : 'div.show-hide-code-arrow.showing-code',
            'Category'    : programCategory,
            'Action'      : 'click - program code - hide',
            'Label'       : function (e) {
              return window.location.pathname;
            },
          }
        ];
        
        elementList.push.apply(elementList,
          HelperFunctions.createTripleElementProgram('recommendations', 'similar', programCategory));
        elementList.push.apply(elementList,
          HelperFunctions.createTripleElementProgram('specific-programs-recommendations', 'recommended', programCategory));
        
        // Social interactions
        let socialInteraction = [
          {
            /**
             * Tracks the click thumpsUp button
             */
            'BaseSelector': '#program-like-thumbs-up',
            'Category'    : 'engagement',
            'Action'      : 'socialEngagement - program',
            'Label'       : window.location.pathname,
            'Value'       : 10,
          },
          {
            /**
             * Tracks the click smile button
             */
            'BaseSelector': '#program-like-smile',
            'Category'    : 'engagement',
            'Action'      : 'socialEngagement - program',
            'Label'       : window.location.pathname,
            'Value'       : 10,
          },
          {
            /**
             * Tracks the click love button
             */
            'BaseSelector': '#program-like-love',
            'Category'    : 'engagement',
            'Action'      : 'socialEngagement - program',
            'Label'       : window.location.pathname,
            'Value'       : 10,
          },
          {
            /**
             * Tracks the click love button
             */
            'BaseSelector': '#program-like-wow',
            'Category'    : 'engagement',
            'Action'      : 'socialEngagement - program',
            'Label'       : window.location.pathname,
            'Value'       : 10,
          },
          {
            /**
             * Tracks the click comment button
             */
            'BaseSelector': '#program-comments button#post-button',
            'Category'    : 'engagement',
            'Action'      : 'socialEngagement - program',
            'Label'       : window.location.pathname,
            'Value'       : 30,
          },
        ];
        
        let trackingObjectList = TrackingObjectFactory.createArray(elementList);
        AnalyticsTracker.registerElementsForClickTracking(trackingObjectList);
        
        let trackingObjectListSocialInteraction = TrackingObjectFactory.createArray(socialInteraction);
        AnalyticsTracker.registerElementsForClickTracking(trackingObjectListSocialInteraction);
        
      }
      
      if (/.*\/search.*/.test(window.location.pathname))
      {
        
        let searchCategory = 'search page';
        let actionSubstring = ((window.location.pathname.indexOf('/tag/search') >= 0) ? "tag" : "program");
        
        let elementList = [
          {
            /**
             * Tracks the clicks on a searched program
             */
            'BaseSelector': '.programs',
            'SubSelector' : 'a',
            'Category'    : searchCategory,
            'Action'      : 'click - searched ' + actionSubstring,
            'Label'       : function (e) {
              return HelperFunctions.removeDomainAndQuery($(e).attr('href'));
            },
          },
          {
            /**
             * Tracks the clicks on the more button of the searched program
             */
            'BaseSelector': '#search-results',
            'SubSelector' : '.button-show-more',
            'Category'    : searchCategory,
            'Action'      : 'click - searched ' + actionSubstring + ' - more',
          },
          {
            /**
             * Tracks the clicks on the less button of the searched program
             */
            'BaseSelector': '#search-results',
            'SubSelector' : '.button-show-less',
            'Category'    : searchCategory,
            'Action'      : 'click - searched ' + actionSubstring + ' - less',
          },
        ];
        
        let trackingObjectList = TrackingObjectFactory.createArray(elementList);
        AnalyticsTracker.registerElementsForClickTracking(trackingObjectList);
      }
      
      if (/.*\/pocket-library.*/.test(window.location.pathname))
      {
        
        
        // tracks the internal downloads
        let libraryDownload = TrackingObjectFactory.createObj(
          {
            'BaseSelector': 'a[onclick^="onDownload',
            'Category'    : 'download',
            'Action'      : 'click - ' + window.location.pathname,
            'Label'       : function (e) {
              let dataHref = $(e).attr('data-href');
              return HelperFunctions.getParameterByName('fname', dataHref);
            }
          });
        
        jQuery(libraryDownload.baseSelector).each(function () {
          $(this).attr('data-href', $(this).attr('href'));
        });
        
        //let libraryDownloadList = [];
        //libraryDownloadList.push(libraryDownload);
        AnalyticsTracker.registerElementsForClickTracking(libraryDownload);
        
      }
      
      if (/.*\/profile.*/.test(window.location.pathname))
      {
        
        let profileCategory = 'profile page';
        let profileElements = [
          {
            /**
             * Tracks the click on the delete button
             */
            'BaseSelector': '#myprofile-programs',
            'SubSelector' : '.img-delete',
            'Category'    : profileCategory,
            'Action'      : 'click - delete own program',
          },
          {
            /**
             * Tracks the click on the make invisible button
             */
            'BaseSelector': '#myprofile-programs',
            'SubSelector' : '.img-lock-open',
            'Category'    : profileCategory,
            'Action'      : 'click - set invisible own program',
          },
          {
            /**
             * Tracks the click on the make visible button
             */
            'BaseSelector': '#myprofile-programs',
            'SubSelector' : '.img-lock',
            'Category'    : profileCategory,
            'Action'      : 'click - set visible own program',
          }
        ];
        
        let profileObjectList = TrackingObjectFactory.createArray(profileElements);
        AnalyticsTracker.registerElementsForClickTracking(profileObjectList);
        
      }
      // end of tracked events
    });
    
  })(window.jQuery, window, document, configGA = configGA || {});
  
}
