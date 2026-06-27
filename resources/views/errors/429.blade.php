@extends('errors.layout')

@section('title', 'Too many requests')
@section('code', '429')
@section('heading', 'Please Slow Down')
@section('message', 'There were too many requests in a short time. Please wait a moment before trying again.')
