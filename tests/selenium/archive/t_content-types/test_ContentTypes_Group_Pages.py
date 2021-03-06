from selenium import selenium
import unittest, time, re, sys, getopt
sys.path.append("shared")
import functions

class test_ContentTypes_Group_Pages(unittest.TestCase):
    def setUp(self):
        self.verificationErrors = []
        self.selenium = selenium(connection["host"], connection["port"], connection["browser"], connection["url"])
        self.selenium.start()

    def test_contenttypes_group_pages(self):
        sel = self.selenium
        sel.open("/")
        sel.click("id=p4cms-site-toolbar-stack-controller_p4cms_ui_toolbar_ContentPane_0_label")
        sel.click("css=input.dijitOffScreen")
        sel.click("//div[@id='p4cms_ui_toolbar_ContentPane_0']/div/ul/li/ul/li[2]/a/span")
        sel.wait_for_page_to_load("30000")
        sel.click("id=group-groups-Pages")
        functions.waitForTextPresent(sel,"Basic Page")
        try: 
           self.failUnless(sel.is_text_present("Basic Page"))
           functions.printResults("265","pass")
        except AssertionError, e:
           self.verificationErrors.append(str(e))
           functions.printResults("265","fail")
        try: 
           self.failUnless(sel.is_text_present("Blog Post"))
           functions.printResults("265","pass")
        except AssertionError, e:
           self.verificationErrors.append(str(e))
           functions.printResults("265","fail")
        try: 
           self.failUnless(sel.is_text_present("Press Release"))
           functions.printResults("265","pass")
        except AssertionError, e:
           self.verificationErrors.append(str(e))
           functions.printResults("265","fail")
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":

    connection = functions.connect(sys.argv)
    del sys.argv[1:]
   
    detailids = ("265")
    functions.printDetailIds(detailids)

    unittest.main()
