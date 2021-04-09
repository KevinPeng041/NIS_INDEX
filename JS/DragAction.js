function Position(x,y) {
    this.X=x?x:0;
    this.Y=y?y:0;

    this.add=function (val) {
        if (val){
            if (!isNaN(val.X))this.X+=val.X;
            if (!isNaN(val.Y))this.Y+=val.Y;
        }
        return this;
    };
    this.subtract=function (val) {
        if (val){
            if (!isNaN(val.X))this.X-=val.X;
            if (!isNaN(val.Y))this.Y-=val.Y;
        }
        return this;
    };
    this.min=function (val) {
        if (!val)return this;
        if (!isNaN(val.X))this.X=Math.min(this.X,val.X);
        if (!isNaN(val.Y))this.Y=Math.min(this.Y,val.Y);
        return this;
    };
    this.max=function (val) {
        if(!val)return this;
        if (!isNaN(val.X))this.X=Math.max(this.X,val.X);
        if (!isNaN(val.Y))this.Y=Math.max(this.Y,val.Y);
        return this;
    };
    this.apply=function (element,control) {
        if (!element)return;

        if (!isNaN(this.X)){
            if (control && (control.orientation != 'horizontal' || this.X >control.upperLimit || this.X <control.lowerLimit)) return;

            element.style.left=this.X+'px';
            //element.style.left=Math.max(Math.min(this.X,area.right),area.left)+'px';
        }
        if (!isNaN(this.Y)){
            if (control && (control.orientation !='vertical' || this.Y >control.upperLimit ||this.Y <control.lowerLimit))return;
            element.style.top= this.Y+'px';
          //  element.style.top= Math.max(Math.min(this.Y,area.bottom),area.top)+'px';
        }

    };
}

function absoluteCursorPosition(e) {
    e=e || window.event;
    if (isNaN(window.scrollX))
        return new Position(e.clientX+document.documentElement.scrollLeft+document.body.scrollLeft,e.clientY+document.documentElement.scrollTop+document.body.scrollTop);
    else

        return new Position(e.clientX+window.scrollX,e.clientY+window.scrollY);
}

function dragObject(element, startCallback, moveCallback, endCallback) {
    if (!element)return;
    var cursorStartPos = null;
    var elementStartPos = null;//元件開始位置
    var dragging = false;
    var control;

    element.addEventListener("mousedown",dragStart,false);


    function dragStart(e) {
        e=e|| window.event;
        if ((e.which && e.which !=1) || (e.button && e.button !=1)) return; //only allow mouse left key

        if(dragging)return;
        dragging=true;
        if (startCallback) control=startCallback(e,element);

        cursorStartPos=absoluteCursorPosition(e);

        console.log(e.clientX,e.clientY,element.style.left,e);
        elementStartPos=new Position(parseInt(element.style.left),parseInt(element.style.top));


        document.addEventListener("mousemove",dragGo,false);
        document.addEventListener("mouseup",dragStop,false);

    }

    function dragGo(e) {
        if (!dragging)return;


        let newPos=absoluteCursorPosition(e).add(elementStartPos).subtract(cursorStartPos);

        newPos.apply(element,control);
        if (moveCallback) moveCallback(e,element,newPos);
    }
    function dragStop(e) {
        if (!dragging) return;
        dragging=false;
        cursorStartPos=null;
        elementStartPos=null;
        if (endCallback)endCallback(e,element);

        document.removeEventListener("mousemove",dragGo,false);
        document.removeEventListener("mouseup",dragStop,false);
    }
    this.dispose=function () {
        element.removeEventListener("mousedown",dragStart,false);
    };
}
