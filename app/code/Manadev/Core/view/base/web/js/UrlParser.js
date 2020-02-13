define(['module', 'Manadev_Core/js/functions/class', 'Manadev_Core/js/Data', 'Manadev_Core/js/functions/startsWith'],
function(module, class_, Data, startsWith) {
    return class_(module.id, Data, {
        parseUrl: function(url) {
            var result = document.createElement('a');
            result.href = url;

            return result;
        },
        parseQuery: function(query) {
            var result = {};

            if (!query) {
                return result;
            }

            if (startsWith(query, '?')) {
                query = query.substr(1);
            }

            query.split('&').forEach(function(param) {
                var pair = param.split('=');

                result[decodeURIComponent(pair[0])] = pair[1] ? decodeURIComponent(pair[1]) : '';
            });

            return result;
        },
        parseHash: function(hash) {
            var result = {};

            if (startsWith(hash, '#')) {
                hash = hash.substr(1);
            }

            var params = hash.split('#');
            for (var i = 0; i < Math.ceil(params.length / 2); i++) {
                if (!params[i]) {
                    continue;
                }

                result[params[i]] = params[i + 1];
            }

            return result;
        },
        generateHash: function(hash) {
            var result = '';

            for (var key in hash) {
                if (!hash.hasOwnProperty(key)) continue;

                result += '#' + key + '#' + hash[key];
            }

            return result;
        }


    });
});