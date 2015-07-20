/*
jquery.scc-cache-info.js
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/
;jQuery(document).ready(function($){
    return $('#scc-dashboard').each(function(){
        $("span[data-scc]").css('display', 'none');    
        $.ajax({
            url: scc.endpoint + '?action=' + scc.action + '&nonce=' + scc.nonce,
            dataType: 'jsonp',
		  	cache: false,
            success: function(res){
                        if(res){
                            
                            $("span[data-scc='pc']").text(res.post_count);
                            $("span[data-scc='pfcc']").text(res.primary.full_cache_count);
                            $("span[data-scc='ppcc']").text(res.primary.partial_cache_count);
                            $("span[data-scc='pncc']").text(res.primary.no_cache_count);
                            $("span[data-scc='pcs']").text(res.primary.cache_status);
                 
                            $("span[data-scc='sfcc']").text(res.secondary.full_cache_count);
                            $("span[data-scc='spcc']").text(res.secondary.partial_cache_count);
                            $("span[data-scc='sncc']").text(res.secondary.no_cache_count);
                            $("span[data-scc='scs']").text(res.secondary.cache_status);
                            
		  					if(res.share_delta.twitter > 0){
		  						$("span[data-scc='twitter']").html(res.share_count.twitter + ' (<span class="delta-rise">+' + res.share_delta.twitter + '</span>)');
							} else if(res.share_delta.twitter < 0){
		  						$("span[data-scc='twitter']").html(res.share_count.twitter + ' (<span class="delta-fall">' + res.share_delta.twitter + '</span>)');
							} else {
		  						$("span[data-scc='twitter']").html(res.share_count.twitter);
							}
	  						if(res.share_delta.facebook > 0){
							  	$("span[data-scc='facebook']").html(res.share_count.facebook + ' (<span class="delta-rise">+' + res.share_delta.facebook + '</span>)');							  
							} else if(res.share_delta.facebook < 0){
							  	$("span[data-scc='facebook']").html(res.share_count.facebook + ' (<span class="delta-fall">' + res.share_delta.facebook + '</span>)');							  
							} else {
							  	$("span[data-scc='facebook']").html(res.share_count.facebook);							  
							}
			   				
	  						if(res.share_delta.gplus > 0){
							  	$("span[data-scc='gplus']").html(res.share_count.gplus + ' (<span class="delta-rise">+' + res.share_delta.gplus + '</span>)');
							} else if(res.share_delta.gplus < 0){
							  	$("span[data-scc='gplus']").html(res.share_count.gplus + ' (<span class="delta-fall">' + res.share_delta.gplus + '</span>)');
							} else {
							  	$("span[data-scc='gplus']").html(res.share_count.gplus);
							}
	  
	  						if(res.share_delta.pocket > 0){
							  	 $("span[data-scc='pocket']").html(res.share_count.pocket + ' (<span class="delta-rise">+' + res.share_delta.pocket + '</span>)');
							} else if(res.share_delta.pocket < 0){
							  	 $("span[data-scc='pocket']").html(res.share_count.pocket + ' (<span class="delta-fall">' + res.share_delta.pocket + '</span>)');
							} else {
							  	 $("span[data-scc='pocket']").html(res.share_count.pocket);
							}                            
	  
      	  					if(res.share_delta.hatebu > 0){
							  	$("span[data-scc='hatebu']").html(res.share_count.hatebu + ' (<span class="delta-rise">+' + res.share_delta.hatebu + '</span>)');
							} else if(res.share_delta.hatebu < 0){
							  	$("span[data-scc='hatebu']").html(res.share_count.hatebu + ' (<span class="delta-fall">' + res.share_delta.hatebu + '</span>)');
							} else {
							  	$("span[data-scc='hatebu']").html(res.share_count.hatebu);
							}                      
	  
      	  					if(res.share_delta.total > 0){
							  	$("span[data-scc='total']").html(res.share_count.total + ' (<span class="delta-rise">+' + res.share_delta.total + '</span>)');
							} else if(res.share_delta.total < 0){
							  	$("span[data-scc='total']").html(res.share_count.total + ' (<span class="delta-fall">' + res.share_delta.total + '</span>)');
							} else {
							  	$("span[data-scc='total']").html(res.share_count.total);
							}                      
	  	  
                            $(".loading").css('display', 'none');
                            $("span[data-scc]").fadeIn();                            
                        } else {
                            $("span[data-scc]").text('?');
                        }
                    },
            error: function(res){
                    	$("span[data-scc]").text('?');
                    }
        });
    });
});