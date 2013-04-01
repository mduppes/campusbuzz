import pprint
import urllib2
import re
import random
import sys
import json
import time


def get_official_ids(fbsecret):

    response1 = urllib2.urlopen("http://wiki.ubc.ca/UBC_Facebook_Accounts")
    data1 = response1.read()

    #pprint.pprint(data1);

    official_sources = re.findall(r'<a rel="nofollow".*facebook\.com/(.*?)">(.*)</a>', data1)
    #all_buildings_url = re.findall(r'<a rel="nofollow"', data1)

    pprint.pprint(official_sources);

    for (path, name) in official_sources:
        try:
            id = None
            if path.startswith("pages/"):
                id = re.findall(r'.*/(\d+)', path)
                id = id[0]
            else:
                url = "https://graph.facebook.com/" + path + "?fields=id&access_token=" + fbsecret
                #print "url: " + url + "\n"
                fbid_response = urllib2.urlopen(url)
                id = fbid_response.read()
                id = json.loads(id)['id']


            print '"' + id + '" => "' + name + '",'
        except:
            continue

fbsecret = sys.argv[1]
get_official_ids(fbsecret)
