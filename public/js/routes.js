var baseURL = '';

url = function(url) {
    if (!baseURL) baseURL = $('head').data('url');
    return baseURL + '/' + url;
};

getCurrentURL = function() {
    return [location.protocol, '//', location.host, location.pathname].join('');
};

routes = {
    ranking: {
        json: function(start, end) { return getCurrentURL() + '.json' + '?start=' + start + '&end=' + end; },
        excel: function(type, start, end) { return getCurrentURL() + '.excel' + '?type=' + type + '&start=' + start + '&end=' + end; }
    },

    ride: function(id){
        return '/admin/rides/'+id;
    }
};