<?php


class LocationMapSolrDataRetriever extends SolrDataRetriever {


  protected function getSolrBaseUrl() {
    return "http://localhost:8983/solr/LocationMap/";
  }

}