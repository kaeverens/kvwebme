/*
 * the functions in this file help to "lazy-load" functions. for example, if
 * you have 400 functions but only 50 of them are needed immediately, then it
 * makes sense to only load those 50 and have "stubs" in place for the rest,
 * allowing them to be loaded when needed.
 *
 * see /docs/license.txt for licensing
 *
 */

function lazyload_replace_stub(fname,js,ps){ // replace stub with function, then call function with original parameters
	eval(js);
	(eval("window."+fname)).apply(this,ps);
}

var i,funcs=[],fname;
for(i=0;i<llStubs.length;++i){
	fname=llStubs[i];
	funcs.push('window.'+fname+'=function(){var ps=arguments;x_kfm_getJsFunction("'+fname+'",function(js){lazyload_replace_stub("'+fname+'",js,ps);});};');
}
eval(funcs.join("\n"));
funcs=null;
i=null;
fname=null;
