function pingpoliColorSelector( parent , options )
{
    // create the default style for the color selector (only once)
    if ( document.getElementById("pingpoliColorSelectorStyle") === null )
    {
        var style = document.createElement("style");
        style.id = "pingpoliColorSelectorStyle";
        style.innerHTML = "pingpoliColorSelectorContainer{display:grid;grid-template-columns:repeat(8,1fr);position:absolute;left:0;top:0;z-index:1060;padding:5px;background-color:#eeeeee;border:1px solid #dddddd;}pingpoliColorSelectorColor{display:inline-block;width:30px;height:30px;margin:5px;border:1px solid #000000;}pingpoliColorSelectorColor:hover{box-shadow:0 0 10px #555555;}";
        document.body.appendChild( style );
    }

    this.parent = parent;

    // if the parent element doesn't have a css class, give it a default style
    if ( this.parent.className == "" )
    {
        this.parent.style.cssText = "display:inline-block;width:200px;height:30px;border:1px solid #000000;";
    }

    // if the options include a custom color list, set it or use the default
    if ( options !== undefined && options.colorList !== undefined ) this.colorList = options.colorList;
    else this.colorList = [ '#ff0000' , '#00ff00' , '#0000ff' , '#00ffff' , '#ff00ff' , '#ffff00' ];

    // if the options include a custom class for the color, set it
    if ( options !== undefined && options.cssColorClass !== undefined ) 
    {
        this.cssColorClass = options.cssColorClass;
    }
    else this.cssColorClass = null;

    // create the popup container element
    this.container = document.createElement("pingpoliColorSelectorContainer");

    // if the options include a custom class for the container, set it
    if ( options !== undefined && options.cssContainerClass !== undefined ) 
    {
        this.container.className = options.cssContainerClass;
    }

    // add the colors to the container
    this.updateColors();

    // finally append the hidden container to the dom
    this.container.style.display = "none";
    this.container.style.left = 0;
    this.container.style.top = 0;
    document.body.appendChild( this.container );
}



pingpoliColorSelector.toggle = function( elementID )
{
    var element = document.getElementById(elementID);
    if ( element.colorSelector === undefined )
    {
        console.log( "pingpoliColorSelector > toggle() > the color selector is not initialized, please call pingpoliColorSelector.init(...) first" );
        return;
    }
    if ( element.colorSelector.container.style.display === "none" )
    {
        // move the container element below the parent
        var bb = element.getBoundingClientRect();
        element.colorSelector.container.style.left = bb.left+"px";
        element.colorSelector.container.style.top = bb.top+element.offsetHeight+"px";
        element.colorSelector.container.style.display = "grid";
    }
    else
    {
        element.colorSelector.container.style.display = "none";
    }
}



pingpoliColorSelector.init = function( elementID , color , options )
{
    var element = document.getElementById(elementID);
    if ( element.colorSelector === undefined )
    {
        element.colorSelector = new pingpoliColorSelector( element , options );
        // attach the setColor funtion directly to the element as well for convenience
        element.setColor = ( color ) => {
            element.colorSelector.setColor( color );
        };
    }
    element.colorSelector.setColor( color );
}



pingpoliColorSelector.prototype.setColor = function( color )
{
    this.parent.colorSelector.color = color;
    this.parent.style.backgroundColor = color;
    this.parent.value = color;
}



pingpoliColorSelector.prototype.updateColors = function()
{
    this.container.textContent = "";
    var fragment = document.createDocumentFragment();
    for ( let i = 0 ; i < this.colorList.length ; ++i )
    {
        var div = document.createElement("pingpoliColorSelectorColor");
        if ( this.cssColorClass !== null )
        {
            div.className = this.cssColorClass;
        }
        div.style.backgroundColor = this.colorList[i];
        div.onclick = () => {
            // set the color
            this.setColor( this.colorList[i] );
            // hide the container
            this.container.style.display = "none";
        };
        fragment.appendChild( div );
    }
    this.container.appendChild( fragment );
}