<script src="{{asset('assets/pspdfkit.js')}}"></script>

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
<div id="{{$key}}" style="height: 100vh;width:61.5vw;"></div>
<script>
	var path = '{{ $document }}';
    var keycontainer = '#'+'{{ $key }}';
	console.log(path);
	PSPDFKit.load({
		container: `${keycontainer}`,
  		document: `{{asset('storage/${path}')}}` // Add the path to your document here.
	})
	.then(function(instance) {
		console.log("PSPDFKit loaded", instance);
	})
	.catch(function(error) {
		console.error(error.message);
	});
</script>
</x-dynamic-component>
