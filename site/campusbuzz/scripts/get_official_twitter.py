import pprint
import urllib2
import re
import random
import sys
import json
import time


def get_official_ids():

    response1 = urllib2.urlopen("http://wiki.ubc.ca/UBC_Twitter_Accounts")
    data1 = response1.read()

    #pprint.pprint(data1);

    official_sources = re.findall(r'<a rel="nofollow".*twitter\.com.*/(.*)">(.*)</a>', data1)
    #all_buildings_url = re.findall(r'<a rel="nofollow"', data1)

    pprint.pprint(official_sources);

    for (path, name) in official_sources:
        try:
            id = path


            print '"' + id + '" => "' + name + '",'
        except:
            continue

get_official_ids()
