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
                            
                            $("span[data-scc='twitter']").text(res.share_count.twitter);
                            $("span[data-scc='facebook']").text(res.share_count.facebook);
                            $("span[data-scc='gplus']").text(res.share_count.gplus);
                            $("span[data-scc='pocket']").text(res.share_count.pocket);
                            $("span[data-scc='hatebu']").text(res.share_count.hatebu);
                            $("span[data-scc='total']").text(res.share_count.total);

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