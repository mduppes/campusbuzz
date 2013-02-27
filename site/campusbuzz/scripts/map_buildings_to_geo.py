import pprint
import urllib2
import re
import random
import sys
import json
import time

# extracts buildings from UBC building list at:
# http://www.maps.ubc.ca/PROD/buildingsListAll.php
def mapLocationsToGeoCoords():
    
    response1 = urllib2.urlopen("http://www.maps.ubc.ca/PROD/buildingsListAll.php")
    data1 = response1.read()

    all_buildings_url = re.findall(r'<li><a href="(.*)">(.*)</a>', data1)
    
    for building in all_buildings_url:
        response_building = urllib2.urlopen("http://www.maps.ubc.ca/PROD/" + building[0])
        data_building = response_building.read()
        match = re.search(r'<h2>(.*)</h2>', data_building)
        address = match.group(1)
    
        formatted_address = re.sub(' ', '+', address)
        response_google_geocode_api = urllib2.urlopen(
            "http://maps.googleapis.com/maps/api/geocode/json?address=" + formatted_address + "+Vancouver+Canada&sensor=false")
        json_response = json.loads(response_google_geocode_api.read())
        if json_response['status'] == 'OK':
            lat = json_response['results'][0]['geometry']['location']['lat']
            long = json_response['results'][0]['geometry']['location']['lng']
            print "{0},{1},{2},{3}".format(building[1], address, lat, long)
        else:
            print "error in google api response for", building[1], address
            pprint.pprint(json_response)

# mostly duplicate code for same purposes of extracting code and buildings from:
# http://www.students.ubc.ca/classroomservices/buildings-and-classrooms/
def mapLocationsWithBuildingCodesToGeoCoords():
    
    response1 = urllib2.urlopen("http://www.students.ubc.ca/classroomservices/buildings-and-classrooms/")
    data1 = response1.read()

    # handle newlines between table rows
    regex = re.compile(r"<tr>(.*?)</tr>", re.DOTALL)
    all_buildings = regex.findall(data1)

    for building in all_buildings:
        # extract all rows of multilined table
        regex_row = re.compile(r"<td>(.*?)</td>", re.DOTALL)
        building_decomposed = regex_row.findall(building)

        # each building has 4 columns: code, building name, address, more info
        # use first 3
        building_code = re.match(r".*>(.*)</a>", building_decomposed[0])
        building_name = re.match(r".*>(.*)</a>", building_decomposed[1])
        building_address = building_decomposed[2]


        formatted_address = re.sub(' ', '+', building_address)
    
        #print formatted_address
        response_google_geocode_api = urllib2.urlopen(
            "http://maps.googleapis.com/maps/api/geocode/json?address=" + formatted_address + "+Vancouver+Canada&sensor=false")
        json_response = json.loads(response_google_geocode_api.read())
        if json_response['status'] == 'OK':
            lat = json_response['results'][0]['geometry']['location']['lat']
            long = json_response['results'][0]['geometry']['location']['lng']
            print "{0},{1},{2},{3},{4}".format(building_name.group(1), building_address, lat, long, building_code.group(1))
        else:
            print "error in google api response for", building_name, building_address
            pprint.pprint(json_response)
        time.sleep(2)
    


def aaa():
    # Relevant fields to extract from login page
    view_state = None

    # extract view state to be sent back on login request
    match = re.search(r"(__VIEWSTATE\" value=\")(.*?)(\")", data1)
    if match != None:
    #print match.groups()
        view_state = match.group(2)
        #print view_state


    if view_state == None:
        print "Error retrieving view state"
        return

    params = (('__EVENTTARGET', ''), 
              ('__EVENTARGUMENT', ''),
              ('__VIEWSTATE', view_state),
              ('ctl00$MainContent$CallingCodeDropDownList', '-1'),
              ('ctl00$MainContent$MobileNumberTextBox', phone_num),
              ('ctl00$MainContent$PinTextBox', password),
              ('ctl00$MainContent$LoginButton', "log in"),
              ('__VIEWSTATEENCRYPTED', ''))
    #print params

    urlencoded_params = urllib.urlencode(params)

    #Send post request
    response2 = opener.open(url, urllib.urlencode(params))


    login_headers = response2.info().headers
    login_response = response2.read()    

    #choose location means login succeeded
    match = re.search("tranrpt1.aspx", login_response)
    if match != None:
        return True
    else:
        return False




#mapLocationsToGeoCoords()
mapLocationsWithBuildingCodesToGeoCoords()
