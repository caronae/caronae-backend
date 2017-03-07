var baseURL = '';

url = function(url) {
    if (!baseURL) baseURL = $('head').data('url');
    return baseURL + '/' + url;
};

getCurrentURL = function() {
    return [location.protocol, '//', location.host, location.pathname].join('');
};

routes = {
    datatables: {
        ptBrLanguageFile: url('vendor/DataTables/Portuguese-Brasil.json')
    },

    users: {
        active: url('admin/users.json'),
        banned: url('admin/users.json?banned=true'),

        banish: function(userId) { return url('admin/user/'+userId+'/banish') },
        unban: function(userId) { return url('admin/user/'+userId+'/unban') }
    },

    ranking: {
        json: function(start, end) { return getCurrentURL() + '.json' + '?start=' + start + '&end=' + end; },
        excel: function(type, start, end) { return getCurrentURL() + '.excel' + '?type=' + type + '&start=' + start + '&end=' + end; }
    },

    riders: function(rideId){
        return url('admin/riders/'+rideId);
    }
};