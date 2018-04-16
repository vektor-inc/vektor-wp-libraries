(function (factory) {
  if (typeof module === "object" && typeof module.exports === "object") {
    module.exports = factory(require("jquery"), window, document);
  }
  else {
    factory(jQuery, window, document);
  }
}(function ($, window, document, undefined) {
  // CSSの各幅表記に対する正規表現
  var urlRegex = /url\(['"]*(.*?)['"]*\)/g;

  /**
   * コンストラクタ
   * @param element
   * @param optionsArg
   * @constructor
   */
  var GetBackgroundSize = function(element, callback){
    this.$element = $(element);
    this.backgroundSize = this.$element.css('background-size').split(' ');
    this.img = new Image();
    this.callback = callback.bind(element);
  };

  /**
   * サイズ最適化
   * @param origin
   * @param current
   * @param target
   * @returns {number}
   */
  GetBackgroundSize.prototype.size_optimization = function(origin, current, target){
    return target * (current/origin);
  };

  /**
   * 要素幅取得
   * @returns {boolean}
   */
  GetBackgroundSize.prototype.getBackgroundWidth = function() {
    var pxRegex = /px/, percentRegex = /%/;

    if (pxRegex.test(this.backgroundSize[0])) {
      this.width = parseInt(this.backgroundSize[0])
    }
    if (percentRegex.test(this.backgroundSize[0])) {
      this.width = this.$element.width() * (parseInt(this.backgroundSize[0]) / 100);
    }
    return (typeof this.width != 'undefined');
  };

  /**
   * 要素高さ取得
   * @returns {boolean}
   */
  GetBackgroundSize.prototype.getBackgroundHeight = function() {
    var pxRegex = /px/, percentRegex = /%/;

    // 背景高さ取得
    if (pxRegex.test(this.backgroundSize[1])) {
      this.height = parseInt(this.backgroundSize[1]);
    }
    if (percentRegex.test(this.backgroundSize[1])) {
      this.height = this.$element.height() * (parseInt(this.backgroundSize[1]) / 100);
    }
    return (typeof this.height != 'undefined');
  };

  /**
   * 生画像の情報を入手
   */
  GetBackgroundSize.prototype.getNaturalImageProperties = function(){
    var _this = this;
    this.img.onload = function () {

      if (typeof _this.width == 'undefined') {
        if (typeof _this.height != 'undefined') {
          _this.width = _this.size_optimization(this.naturalHeight, _this.height, this.naturalWidth);
        }else{
          _this.width = _this.$element.width();
        }
      }

      if (typeof _this.height == 'undefined') {
        if (typeof _this.width != 'undefined') {
          _this.height = _this.size_optimization(this.naturalWidth, _this.width, this.naturalHeight);
        }else {
          _this.height = _this.$element.height();
        }
      }

      if(_this.backgroundSize[0] == "cover") {
        /* 縦横比比較 */
        if(_this.$element.width()/_this.$element.height() > _this.width/_this.height){
          _this.width = _this.$element.width();
          _this.height = _this.size_optimization(this.naturalWidth, _this.width, this.naturalHeight);
        }else{
          _this.height = _this.$element.height();
          _this.width = _this.size_optimization(this.naturalHeight, _this.height, this.naturalWidth);
        }
      }
      _this.callback({width: _this.width, height: _this.height});
    };

    /* Call img.onload to refer natural size. */
    this.img.src = this.$element.css('background-image').replace(urlRegex, '$1');
  };

  /**
   * 初期化処理
   * @returns {{width: *, height: *}}
   */
  GetBackgroundSize.prototype.init = function(){

    // 高さおよび幅が両方与えられていたらそのままReturn
    var rst = [this.getBackgroundHeight(), this.getBackgroundWidth()];

    if(rst[0] && rst[1]) {
      this.callback({width: this.width, height: this.height});
      return;
    }
    return this.getNaturalImageProperties();
  };

  $.fn.getBackgroundSize = function (callback){
    return this.each(function(){
      new GetBackgroundSize(this, callback).init();
    });
  }
}));
