/*********************************************************************************
 * Copyright 2009 Priacta, Inc.
 * 
 * This software is provided free of charge, but you may NOT distribute any
 * derivative works or publicly redistribute the software in any form, in whole
 * or in part, without the express permission of the copyright holder.
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *********************************************************************************/

Manual Install Instructions for Post Affiliate Pro Connector

1. Merge the files and directories into the root of your Magento installation.
2. If you are using Magento 1.3.x or earlier, merge the contents of app/design/frontend/base into your theme folder (I.E. app/design/frontend/yourthemename).
3. Go to System->Cache Management and refresh the cache (alternately, you can delete the var/cache folder via FTP)
4. Go to System->Permissions->Roles and add Configuration->Post Affiliate Pro
   to any administrators. Any roles with "Resource Access: All" need to
   be re-saved to aquire access to the Post Affiliate Pro configuration.
   WARNING: ONLY administrators should be given permission to configure Post Affiliate Pro
5. Log Out of the Magento Admin area, and then log back in.
6. You should have a new message in your Inbox with information on completing the
   setup and configuration. Read the details and follow the instructions, if any.