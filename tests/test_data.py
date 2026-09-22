#!/usr/bin/env python3
"""
Tourism Management System - Data Verification Test
Tests that the website displays realistic data correctly.
"""

import urllib.request
import urllib.error

BASE_URL = "http://localhost:8000/"

def test_page(url, expected_title_contains, expected_content=None):
    """Test a page loads and contains expected content."""
    try:
        req = urllib.request.Request(url)
        resp = urllib.request.urlopen(req, timeout=5)
        html = resp.read().decode('utf-8')
        
        print(f"Testing: {url}")
        print(f"  Status: HTTP {resp.status}")
        print(f"  Title contains '{expected_title_contains}': {'✓' if expected_title_contains in html else '✗'}")
        
        if expected_content:
            for content in expected_content:
                found = content in html
                print(f"  Contains '{content[:50]}': {'✓' if found else '✗'}")
        
        return resp.status == 200
    except Exception as e:
        print(f"  Error: {e}")
        return False

print("=" * 60)
print("TOURISM MANAGEMENT SYSTEM - DATA VERIFICATION")
print("=" * 60)

# Test homepage
test_page(BASE_URL, "Tourism MS", [
    "Paris City Escape",
    "Tokyo Discovery Tour", 
    "Bali Beach Resort Vacation",
    "Nepal Himalayan Adventure",
    "California Coastal Road Trip"
])

print()

# Test package details
test_page("/package_details.php?id=1", "Paris City Escape", [
    "Eiffel Tower",
    "Louvre Museum",
    "$1,299.00"
])

print()

# Test admin login page
test_page("/admin/login.php", "Admin Login", [
    "Username",
    "Password"
])

print()
print("=" * 60)
print("All tests completed!")
print("=" * 60)
